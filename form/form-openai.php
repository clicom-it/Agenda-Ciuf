<?php
include '../library/controllo.php';
include '../library/config.php';
include '../library/connessione.php';
include '../library/basic.class.php';
include '../library/functions.php';
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<link type="text/css" href="/css/openai.css" rel="Stylesheet" />
<script type="text/javascript" src="./js/functions-openai.js"></script>
<script type="text/javascript">
    $(function () {

    });
</script>
<div class="chiudi"></div>
<div id="contieni-ai" style="font-size: 1em;">
    <!-- Chats container -->
    <div class="chat-container"></div>
    <!-- Typing container -->
    <div class="typing-container">
        <div class="typing-content">
            <div class="typing-textarea">
                <textarea id="chat-input" spellcheck="false" placeholder="Enter a prompt here" required></textarea>
                <span id="send-btn" class="material-symbols-rounded">Invia</span>
            </div>
            <div class="typing-controls">
                <span id="theme-btn" class="material-symbols-rounded">light_mode</span>
                <span id="delete-btn" class="material-symbols-rounded">delete</span>
            </div>
        </div>
    </div>
</div>
<div class="chiudi"></div>