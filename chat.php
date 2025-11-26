<?php
session_start();
require_once 'config.php';

// For demo, fallback login if not set
if(!isset($_SESSION['user_id'])){
    $_SESSION['user_id'] = 1;
    $_SESSION['first_name'] = 'Demo';
    $_SESSION['last_name'] = 'User';
}

$user_id = (int)$_SESSION['user_id'];
$first_name = htmlspecialchars($_SESSION['first_name'], ENT_QUOTES);

// Fetch other users
$users = $conn->query("SELECT user_id, first_name, last_name FROM Users WHERE user_id != $user_id ORDER BY first_name, last_name");

// Selected contact
$selected_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Live Chat</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
<style>
body { background:#f8f9fa; font-family:sans-serif; }
.chat-container { display:flex; height:calc(100vh - 70px); max-height:800px; box-shadow:0 4px 12px rgba(0,0,0,0.1); border-radius:8px; overflow:hidden; }
.user-list { width:280px; background:#fff; border-right:1px solid #ddd; overflow-y:auto; flex-shrink:0; }
.contact { display:flex; gap:12px; align-items:center; padding:12px 16px; border-bottom:1px solid #f1f1f1; cursor:pointer; transition:0.2s; }
.contact:hover { background:#f0f0f0; }
.contact.active { background:#0d6efd; color:#fff; font-weight:bold; }
.contact.active:hover { background:#0a58ca; }
.avatar { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:#eee; font-weight:bold; color:#555; }
.contact.active .avatar { background:#fff; color:#0d6efd; }
.meta { display:flex; flex-direction:column; min-width:0; }
.name { font-weight:600; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.sub { font-size:13px; color:#888; }
.chat-box { flex:1; display:flex; flex-direction:column; background:#fff; }
.chat-header { padding:12px 16px; border-bottom:1px solid #ddd; display:flex; justify-content:space-between; align-items:center; background:#f8f9fa; }
.messages { flex:1; overflow-y:auto; padding:16px; display:flex; flex-direction:column; }
.msg { margin-bottom:12px; display:flex; align-items:flex-end; }
.msg.me { justify-content:flex-end; }
.bubble { padding:10px 14px; border-radius:15px; max-width:80%; word-wrap:break-word; line-height:1.4; box-shadow:0 1px 2px rgba(0,0,0,0.1); }
.msg.me .bubble { background:#0d6efd; color:#fff; border-bottom-right-radius:4px; }
.msg.them .bubble { background:#e9ecef; color:#212529; border-bottom-left-radius:4px; }
.meta-line { font-size:11px; color:#666; margin-top:3px; }
.msg.them .meta-line { text-align:left; }
.msg.me .meta-line { text-align:right; }
.chat-footer { padding:12px 16px; border-top:1px solid #ddd; display:flex; gap:8px; }
.typing { font-size:12px; color:#0d6efd; padding:0 16px 8px; display:none; }
.empty { padding:24px; text-align:center; color:#888; }
@media (max-width:768px){
    .chat-container { flex-direction:column; height:auto; }
    .user-list { width:100%; max-height:150px; border-right:none; border-bottom:1px solid #ddd; }
    .chat-box { min-width:100%; height:75vh; }
}
</style>
</head>
<body>
<nav class="navbar navbar-dark bg-primary mb-3">
<div class="container-fluid">
    <span class="navbar-brand">Live Chat</span>
    <div class="d-flex align-items-center">
        <span class="me-3 text-white">Logged in as <strong><?= $first_name ?></strong></span>
        <a href="logout.php" class="btn btn-sm btn-light">Logout</a>
    </div>
</div>
</nav>

<div class="container">
<div class="chat-container">
    <div class="user-list" id="contactList">
        <!-- AI Assistant -->
        <div class="contact <?= ($selected_id === 0 ? 'active' : '') ?>" data-userid="0">
            <div class="avatar"><i class="fa-solid fa-robot"></i></div>
            <div class="meta"><div class="name">AI Assistant</div><div class="sub">Ask questions</div></div>
        </div>
        <?php while($u=$users->fetch_assoc()): $uid=(int)$u['user_id']; $initial=strtoupper($u['first_name'][0]??'U'); ?>
        <div class="contact <?= ($selected_id===$uid?'active':'') ?>" data-userid="<?= $uid ?>">
            <div class="avatar"><?= htmlspecialchars($initial) ?></div>
            <div class="meta"><div class="name"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></div><div class="sub">Direct message</div></div>
        </div>
        <?php endwhile; ?>
    </div>

    <div class="chat-box">
        <div class="chat-header">
            <div>
                <strong id="chatTitle"><?= $selected_id===0?'AI Assistant':($selected_id?'Conversation':'Select a contact') ?></strong>
                <div class="sub" id="chatSubtitle"><?= $selected_id===0?'AI chat':($selected_id?'Direct message':'Choose someone') ?></div>
            </div>
            <div>
                <button id="btnRefresh" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-sync-alt"></i></button>
                <button id="btnClear" class="btn btn-outline-danger btn-sm ms-2"><i class="fa-solid fa-trash-alt"></i></button>
            </div>
        </div>

        <div class="typing" id="typingIndicator">Typing…</div>
        <div class="messages" id="messages">
            <div class="empty">Select a contact to start chatting.</div>
        </div>

        <form id="chatForm" class="chat-footer">
            <input type="hidden" id="receiver_id" name="receiver_id" value="<?= $selected_id??'' ?>">
            <input id="messageInput" name="message" type="text" class="form-control" placeholder="Type a message…" autocomplete="off" <?= $selected_id===null?'disabled':'' ?>>
            <button class="btn btn-primary" type="submit" <?= $selected_id===null?'disabled':'' ?>>Send</button>
        </form>
    </div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
(function($){
const CHAT_ACTION_URL='chat_action.php';
let selectedUser = <?= $selected_id!==null?json_encode($selected_id):'null' ?>;
let pollInterval = null;

function safeHtml(s){ return $('<div/>').text(s||'').html(); }

function setSelection(uid,name){
    selectedUser=parseInt(uid,10);
    $('#receiver_id').val(selectedUser);
    $('#contactList .contact').removeClass('active');
    $('#contactList .contact[data-userid="'+uid+'"]').addClass('active');
    $('#chatTitle').text(name || (uid===0?'AI Assistant':'Conversation'));
    $('#chatSubtitle').text(uid===0?'AI chat':'Direct message');
    $('#messageInput').prop('disabled',false);
    $('#chatForm button').prop('disabled',false);
    $('#messages').html('<div class="empty">Loading messages…</div>');
    loadMessages(true);
    if(pollInterval) clearInterval(pollInterval);
    pollInterval=setInterval(loadMessages,3000);
}

$('#contactList').on('click','.contact',function(){
    const uid=$(this).data('userid');
    const name=$(this).find('.name').text().trim();
    setSelection(uid,name);
    history.pushState(null,'',`chat.php?user_id=${uid}`);
});

function loadMessages(initial=false){
    const rid=$('#receiver_id').val();
    if(rid===''||rid===null) return;
    $.post(CHAT_ACTION_URL,{action:'load',receiver_id:rid},function(html){
        $('#messages').html(html.length?html:'<div class="empty">No messages yet.</div>');
        if(initial||$('#messages')[0].scrollHeight-$('#messages').scrollTop()-$('#messages').outerHeight()<100){
            $('#messages').scrollTop($('#messages')[0].scrollHeight);
        }
    });
}

$('#chatForm').on('submit',function(e){
    e.preventDefault();
    const rid=$('#receiver_id').val();
    const msg=$('#messageInput').val().trim();
    if(!rid && rid!=='0') return;
    if(!msg) return;
    const meHtml=`<div class="msg me"><div class="bubble"><strong>You:</strong> ${safeHtml(msg)}</div><div class="meta-line">Now • Sending…</div></div>`;
    $('#messages').append(meHtml);
    $('#messageInput').val('');
    $('#messages').scrollTop($('#messages')[0].scrollHeight);
    $('#messageInput').prop('disabled',true);

    $.post(CHAT_ACTION_URL,{action:'send',receiver_id:rid,message:msg},function(res){
        if(res) $('#messages').append(res);
        loadMessages();
        $('#messageInput').prop('disabled',false).focus();
    });
});

$('#btnRefresh').on('click',()=>loadMessages(true));
$('#btnClear').on('click',function(){
    const rid=$('#receiver_id').val();
    if(!rid && rid!=='0') return;
    if(!confirm('Clear conversation?')) return;
    $.post(CHAT_ACTION_URL,{action:'clear',receiver_id:rid},function(){
        $('#messages').html('<div class="empty">Conversation cleared.</div>');
    });
});

})(jQuery);
</script>
</body>
</html>
