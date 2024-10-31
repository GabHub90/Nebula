
<?php
// Avvia il buffering dell'output
ob_start();

// Definisci la funzione per gestire gli errori
function errorHandler($errno, $errstr, $errfile, $errline) {
    // Stampa un messaggio di errore personalizzato
    echo "Si è verificato un errore: $errstr";

    // Puoi fare altre operazioni qui, ad esempio registrare l'errore in un file di log

    // Interrompi l'esecuzione dello script
    exit();
}

// Imposta la funzione di gestione degli errori
set_error_handler("errorHandler");

// Esempio di codice con un errore
echo $undefinedVariable;

// Se il codice raggiunge questo punto, non ci sono stati errori
// Pulisci il buffer di output e invialo al browser
ob_end_flush();
?>
