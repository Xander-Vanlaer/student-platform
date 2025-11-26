<?php
session_start();
require_once 'config.php';
if(!isset($_SESSION['user_id'])) { http_response_code(401); exit('Not logged in'); }

$user_id = (int)$_SESSION['user_id'];
$action = $_POST['action'] ?? '';

function safe_html($s){ return htmlspecialchars($s ?? '',ENT_QUOTES,'UTF-8'); }

function resolve_session(mysqli $conn,int $uid,int $rid):int{
    if($rid===0) return 0;
    $stmt=$conn->prepare("SELECT session_id FROM Chat_Sessions WHERE (user1_id=? AND user2_id=?) OR (user1_id=? AND user2_id=?) LIMIT 1");
    $stmt->bind_param("iiii",$uid,$rid,$rid,$uid);
    $stmt->execute();
    $res=$stmt->get_result();
    if($res && $res->num_rows>0) return (int)$res->fetch_assoc()['session_id'];
    $a=min($uid,$rid); $b=max($uid,$rid);
    $stmt=$conn->prepare("INSERT INTO Chat_Sessions(user1_id,user2_id) VALUES(?,?)");
    $stmt->bind_param("ii",$a,$b);
    $stmt->execute();
    return (int)$stmt->insert_id;
}

function render_messages(mysqli $conn,int $session,int $uid):string{
    if($session===0){
        $stmt=$conn->prepare("SELECT message_id,sender_id,message,created_at FROM Chat_Messages WHERE session_id=0 ORDER BY created_at ASC,message_id ASC");
    }else{
        $stmt=$conn->prepare("SELECT message_id,sender_id,message,created_at FROM Chat_Messages WHERE session_id=? ORDER BY created_at ASC,message_id ASC");
        $stmt->bind_param("i",$session);
    }
    $stmt->execute();
    $res=$stmt->get_result();
    $html='';
    while($msg=$res->fetch_assoc()){
        $sid=(int)$msg['sender_id'];
        $class=$sid===$uid?'me':'them';
        $sender=$sid===$uid?'You':($sid===0?'AI':'Them');
        $time=safe_html($msg['created_at']);
        $html.="<div class='msg $class'><div class='bubble'><strong>$sender:</strong> ".safe_html($msg['message'])."</div><div class='meta-line'>$time</div></div>";
    }
    return $html;
}

if($action==='load'){
    $rid=(int)($_POST['receiver_id']??-1);
    if($rid<0){ http_response_code(400); exit('Missing receiver_id'); }
    $session=resolve_session($conn,$user_id,$rid);
    echo render_messages($conn,$session,$user_id);
    exit;
}

if($action==='send'){
    $rid=(int)($_POST['receiver_id']??-1);
    $msg=trim($_POST['message']??'');
    if($rid<0 || !$msg){ http_response_code(400); exit('Missing data'); }
    $session=resolve_session($conn,$user_id,$rid);
    $stmt=$conn->prepare("INSERT INTO Chat_Messages(session_id,sender_id,message) VALUES(?,?,?)");
    $stmt->bind_param("iis",$session,$user_id,$msg);
    $stmt->execute();

    if($rid===0){
        $ai_msg="AI reply to: $msg";
        $stmt2=$conn->prepare("INSERT INTO Chat_Messages(session_id,sender_id,message,is_read) VALUES(?,?,?,1)");
        $sid=0; $stmt2->bind_param("iis",$session,$sid,$ai_msg);
        $stmt2->execute();
        echo "<div class='msg them'><div class='bubble'><strong>AI:</strong> ".safe_html($ai_msg)."</div><div class='meta-line'>Now</div></div>";
    }
    exit;
}

if($action==='clear'){
    $rid=(int)($_POST['receiver_id']??-1);
    if($rid<0){ http_response_code(400); exit('Missing receiver_id'); }
    $session=resolve_session($conn,$user_id,$rid);
    $stmt=$conn->prepare("DELETE FROM Chat_Messages WHERE session_id=?");
    $stmt->bind_param("i",$session);
    $stmt->execute();
    echo "OK";
    exit;
}

http_response_code(400);
echo "Invalid action";
?>
