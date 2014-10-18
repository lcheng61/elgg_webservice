<?php
    $pattern = "/308,/";
    $replacement = "";
    $tips = "tmp123, 456123, 456123, 456,123, 456,123, 456,274,306,307,308,309,310,314,315,316,";
    echo ("before preg_replace, $tips\n");
    $tips = preg_replace($pattern, $replacement, $tips);
    echo ("after preg_replace, $tips\n");
?>