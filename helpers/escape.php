<?php
function escape($html) {
    if ($html === null) {
        return '';
    }
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
