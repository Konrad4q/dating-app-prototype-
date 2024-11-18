<?php
session_start();
session_unset();  // Usuwamy wszystkie zmienne sesji
session_destroy();  // Zniszczymy sesję

header("Location: index.php");  // Przekierowanie na stronę główną po wylogowaniu
exit();
