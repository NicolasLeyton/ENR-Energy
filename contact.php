<?php
/**
 * ENR Energy — Gestionnaire de formulaire de contact
 * Hébergé sur LWS (datacenters France, ISO 27001, conforme RGPD)
 * Aucune donnée ne quitte l'Union européenne.
 */

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

/* ── Sécurité : n'accepter que les requêtes POST ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

/* ── Anti-spam : honeypot (le champ "website" doit être vide) ── */
if (!empty($_POST['website'])) {
    // Bot détecté — répondre 200 pour ne pas trahir le piège
    echo json_encode(['success' => true]);
    exit;
}

/* ── Consentement RGPD obligatoire ── */
if (empty($_POST['rgpd_consent']) || $_POST['rgpd_consent'] !== 'oui') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Le consentement RGPD est requis.']);
    exit;
}

/* ── Nettoyage des entrées ── */
function clean(string $val): string {
    return htmlspecialchars(strip_tags(trim($val)), ENT_QUOTES, 'UTF-8');
}

$prenom      = clean($_POST['prenom']      ?? '');
$nom         = clean($_POST['nom']         ?? '');
$email       = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$telephone   = clean($_POST['telephone']   ?? '');
$profil      = clean($_POST['profil']      ?? '');
$projet      = clean($_POST['projet']      ?? '');
$code_postal = clean($_POST['code_postal'] ?? '');
$message     = clean($_POST['message']     ?? '');

/* ── Validation des champs obligatoires ── */
if (!$prenom || !$nom || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$profil || !$projet) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs obligatoires.']);
    exit;
}

/* ── Construction de l'email ── */
$destinataire = 'commercial@enr-energy.fr';
$sujet        = "=?UTF-8?B?" . base64_encode("Nouvelle demande de devis — {$prenom} {$nom}") . "?=";
$expediteur   = 'no-reply@enr-energy.fr';

$corps  = "Nouvelle demande de devis reçue depuis le site enr-energy.fr\n";
$corps .= str_repeat('─', 55) . "\n\n";
$corps .= "Prénom      : {$prenom}\n";
$corps .= "Nom         : {$nom}\n";
$corps .= "Email       : {$email}\n";
$corps .= "Téléphone   : " . ($telephone ?: '—') . "\n";
$corps .= "Profil      : {$profil}\n";
$corps .= "Projet      : {$projet}\n";
$corps .= "Code postal : " . ($code_postal ?: '—') . "\n\n";
$corps .= "Message :\n{$message}\n\n";
$corps .= str_repeat('─', 55) . "\n";
$corps .= "Consentement RGPD : oui\n";
$corps .= "Date              : " . date('d/m/Y à H:i:s') . "\n";
$corps .= "IP                : " . ($_SERVER['REMOTE_ADDR'] ?? '—') . "\n";

$entetes  = "From: ENR Energy <{$expediteur}>\r\n";
$entetes .= "Reply-To: {$prenom} {$nom} <{$email}>\r\n";
$entetes .= "MIME-Version: 1.0\r\n";
$entetes .= "Content-Type: text/plain; charset=UTF-8\r\n";
$entetes .= "Content-Transfer-Encoding: 8bit\r\n";
$entetes .= "X-Mailer: ENR-Energy-Contact/1.0\r\n";

/* ── Envoi ── */
$envoye = mail($destinataire, $sujet, $corps, $entetes);

if ($envoye) {
    /* Email de confirmation au client */
    $sujet_client = "=?UTF-8?B?" . base64_encode("Votre demande de devis ENR Energy") . "?=";
    $corps_client  = "Bonjour {$prenom},\n\n";
    $corps_client .= "Nous avons bien reçu votre demande de devis concernant : {$projet}.\n\n";
    $corps_client .= "Notre équipe vous répondra dans un délai de 48h ouvrées.\n\n";
    $corps_client .= "Pour toute urgence, contactez-nous directement :\n";
    $corps_client .= "  📞 04 76 40 56 69 / 06 46 42 10 22\n";
    $corps_client .= "  ✉️  commercial@enr-energy.fr\n\n";
    $corps_client .= "Cordialement,\nL'équipe ENR Energy\n";
    $corps_client .= "47 Impasse des Noyers, 38530 Barraux\n";

    $entetes_client  = "From: ENR Energy <{$expediteur}>\r\n";
    $entetes_client .= "MIME-Version: 1.0\r\n";
    $entetes_client .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $entetes_client .= "Content-Transfer-Encoding: 8bit\r\n";

    mail($email, $sujet_client, $corps_client, $entetes_client);

    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'envoi. Veuillez nous appeler directement.']);
}
