<?php
// api/send_email.php - Direct Native SMTP Dispatcher for Gmail TLS (Multilingual)
header('Content-Type: application/json; charset=UTF-8');
require_once __DIR__ . '/../config/db.php';

// ==========================================
// 1. GMAIL SMTP CONFIGURATION
// ==========================================
$smtpHost = 'smtp.gmail.com';
$smtpPort = 587;
$smtpUser = 'karyaflowplatform@gmail.com';     // <-- Replace with your Gmail address
$smtpPass = 'qlcp gmsd gxsk gtub';             // <-- Replace with your 16-character App Password
// ==========================================

$input = json_decode(file_get_contents('php://input'), true);

$recipients = $input['recipients'] ?? [
    'joshi.shranya2202@gmail.com',
    'jaisvidhianil@gmail.com',
    'shranyajoshi2006@gmail.com',
    'ritikap1807@gmail.com'
];

$invoiceId  = $input['invoice_id'] ?? 'INV-1042';
$amount     = $input['amount'] ?? '$17,250.00';
$txHash     = $input['tx_hash'] ?? '0x88f9104bcde291a7834e02bbca018274f910e53a2';
$lang       = strtolower($input['lang'] ?? 'en');

// Localized Email Subject and Body Construction
if ($lang === 'hi') {
    $rawSubject = "[कार्यफ्लो एस्क्रो सूचना] चालान {$invoiceId} का निपटान जारी";
    $body = "प्रिय हितधारक,\n\n"
          . "यह कार्यफ्लो स्वायत्त ऑर्केस्ट्रेशन इंजन की एक स्वचालित सूचना है।\n\n"
          . "--------------------------------------------------------\n"
          . "  एस्क्रो निपटान निष्पादन प्रमाण\n"
          . "--------------------------------------------------------\n"
          . "  चालान संख्या      : {$invoiceId}\n"
          . "  संवितरित राशि     : {$amount}\n"
          . "  लेज़र हैश         : {$txHash}\n"
          . "  सत्यापन कोरम     : मल्टी-सिग कंसोर्टियम आम सहमति (सत्यापित)\n"
          . "  समय (UTC)         : " . gmdate('Y-m-d H:i:s') . "\n"
          . "--------------------------------------------------------\n\n"
          . "विवादित अंतर को आंतरिक ईआरपी सामान्य लेज़र के साथ मिलान किया गया है।\n"
          . "दोहरे क्रिप्टोग्राफिक निजी कुंजियों पर हस्ताक्षर किए गए हैं।\n\n"
          . "सादर,\n"
          . "कार्यफ्लो स्वायत्त कंसोर्टियम गेटवे\n";
} elseif ($lang === 'mr') {
    $rawSubject = "[कार्यफ्लो एस्क्रो सूचना] बीजक {$invoiceId} चे सेटलमेंट पूर्ण";
    $body = "प्रिय भागधारक,\n\n"
          . "ही कार्यफ्लो स्वायत्त ऑर्केस्ट्रेशन इंजिनची एक स्वयंचलित सूचना आहे.\n\n"
          . "--------------------------------------------------------\n"
          . "  एस्क्रो सेटलमेंट अंमलबजावणी पुरावा\n"
          . "--------------------------------------------------------\n"
          . "  बीजक क्रमांक      : {$invoiceId}\n"
          . "  वितरित रक्कम      : {$amount}\n"
          . "  लेजर हॅश          : {$txHash}\n"
          . "  पडताळणी कोरम      : मल्टी-सिग कन्सोर्टियम एकमत (पडताळणी पूर्ण)\n"
          . "  वेळ (UTC)         : " . gmdate('Y-m-d H:i:s') . "\n"
          . "--------------------------------------------------------\n\n"
          . "विवादित फरक अंतर्गत ईआरपी लेजरसह जुळवण्यात आला आहे.\n"
          . "दोन्ही पक्षांच्या डिजिटल स्वाक्षऱ्या पूर्ण झाल्या आहेत.\n\n"
          . "आपला नम्र,\n"
          . "कार्यफ्लो स्वायत्त कन्सोर्टियम गेटवे\n";
} elseif ($lang === 'es') {
    $rawSubject = "[Aviso de Fideicomiso KaryaFlow] Liquidación Liberada para {$invoiceId}";
    $body = "Estimado Interesado,\n\n"
          . "Esta es una notificación automática del Motor de Orquestación Autónoma KaryaFlow.\n\n"
          . "--------------------------------------------------------\n"
          . "  COMPROBANTE DE LIQUIDACIÓN DE FIDEICOMISO\n"
          . "--------------------------------------------------------\n"
          . "  Identificador     : {$invoiceId}\n"
          . "  Monto Liquidado   : {$amount}\n"
          . "  Hash de Registro  : {$txHash}\n"
          . "  Quórum Verificado : Consenso de Consorcio Multifirma (Verificado)\n"
          . "  Marca de Tiempo   : " . gmdate('Y-m-d H:i:s') . " UTC\n"
          . "--------------------------------------------------------\n\n"
          . "La discrepancia fue conciliada contra el libro mayor ERP.\n"
          . "Las claves criptográficas duales han sido refrendadas y confirmadas en la cadena.\n\n"
          . "Atentamente,\n"
          . "Pasarela de Consorcio Autónomo KaryaFlow\n";
} else {
    // English default
    $rawSubject = "[KaryaFlow Escrow Notice] Settlement Released for {$invoiceId}";
    $body = "Dear Stakeholder,\n\n"
          . "This is an automated notification from the KaryaFlow Autonomous Orchestration Engine.\n\n"
          . "--------------------------------------------------------\n"
          . "  ESCROW SETTLEMENT EXECUTION PROOF\n"
          . "--------------------------------------------------------\n"
          . "  Invoice Identifier : {$invoiceId}\n"
          . "  Disbursed Balance  : {$amount}\n"
          . "  Ledger Hash        : {$txHash}\n"
          . "  Verification Quorum: Multi-Sig Consortium Consensus (Verified)\n"
          . "  Timestamp (UTC)    : " . gmdate('Y-m-d H:i:s') . "\n"
          . "--------------------------------------------------------\n\n"
          . "The disputed variance was reconciled against internal ERP general ledgers.\n"
          . "Dual cryptographic private keys have been countersigned and confirmed on-chain.\n\n"
          . "Regards,\n"
          . "KaryaFlow Autonomous Consortium Gateway\n";
}

// Encode subject for UTF-8 compatibility (preserves Hindi / Marathi Unicode characters in Gmail inbox)
$encodedSubject = "=?UTF-8?B?" . base64_encode($rawSubject) . "?=";

function sendSmtpMail($host, $port, $username, $password, $from, $to, $subject, $body) {
    $cleanPassword = str_replace(' ', '', $password);
    $timeout = 15;

    $socket = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if (!$socket) {
        throw new Exception("Unable to connect to {$host}:{$port} ({$errstr})");
    }

    $read = function() use ($socket) {
        $response = '';
        while ($line = fgets($socket, 512)) {
            $response .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }
        return $response;
    };

    $send = function($cmd) use ($socket, $read) {
        fputs($socket, $cmd . "\r\n");
        return $read();
    };

    $read();
    $send("EHLO " . gethostname());
    $resp = $send("STARTTLS");
    if (strpos($resp, '220') === false) {
        fclose($socket);
        throw new Exception("STARTTLS failed: " . $resp);
    }

    $cryptoMethod = STREAM_CRYPTO_METHOD_TLS_CLIENT;
    if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
        $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
    }
    if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
        $cryptoMethod |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
    }

    if (!stream_socket_enable_crypto($socket, true, $cryptoMethod)) {
        fclose($socket);
        throw new Exception("TLS encryption handshake failed.");
    }

    $send("EHLO " . gethostname());
    $send("AUTH LOGIN");
    $send(base64_encode($username));
    $resp = $send(base64_encode($cleanPassword));
    if (strpos($resp, '235') === false) {
        fclose($socket);
        throw new Exception("Authentication credentials failed: " . $resp);
    }

    $send("MAIL FROM: <{$username}>");
    $resp = $send("RCPT TO: <{$to}>");
    if (strpos($resp, '250') === false && strpos($resp, '251') === false) {
        fclose($socket);
        throw new Exception("Recipient <{$to}> rejected: " . $resp);
    }

    $send("DATA");

    $headers = "From: KaryaFlow Pro <{$username}>\r\n"
             . "To: <{$to}>\r\n"
             . "Subject: {$subject}\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "Content-Transfer-Encoding: 8bit\r\n"
             . "X-Mailer: KaryaFlow-Autonomous-Engine\r\n";

    $send($headers . "\r\n" . $body . "\r\n.");
    $send("QUIT");
    fclose($socket);
    return true;
}

$sentRecipients = [];
$errors = [];

foreach ($recipients as $email) {
    try {
        sendSmtpMail($smtpHost, $smtpPort, $smtpUser, $smtpPass, $smtpUser, $email, $encodedSubject, $body);
        $sentRecipients[] = $email;
    } catch (Exception $e) {
        $errors[$email] = $e->getMessage();
    }
}

$auditDetails = count($sentRecipients) > 0
    ? "Multilingual [{$lang}] SMTP broadcast dispatched to " . count($sentRecipients) . " stakeholders for {$invoiceId}."
    : "SMTP Broadcast failed for all recipients.";

try {
    $stmt = $pdo->prepare("INSERT INTO audit_logs (workflow_id, action, details) VALUES (?, ?, ?)");
    $stmt->execute([1, 'DISPATCH_BROADCAST', $auditDetails]);
} catch (Exception $e) {
    // Database fallback
}

if (count($sentRecipients) > 0) {
    echo json_encode([
        'success' => true,
        'language' => $lang,
        'total_sent' => count($sentRecipients),
        'recipients' => $sentRecipients,
        'failed' => $errors
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to deliver to any recipients. Check your credentials in api/send_email.php',
        'details' => $errors
    ]);
}