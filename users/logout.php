<?php

echo "
        <form id='logout' method='POST' action='user_handler.php'>
        <input type='hidden' name='logout' value='logout'>
        </form>
        <script>
            document.querySelector('#logout').submit();
        </script>
    ";
