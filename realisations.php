<?php
/* ─────────────────────────────────────────────────────────────────────────
 *  realisations.php — LISTE AUTOMATIQUE DES PHOTOS DE LA GALERIE
 *  ------------------------------------------------------------------------
 *  Ce petit script lit le dossier « realisations » et renvoie la liste des
 *  photos qu'il contient (au format JSON). La galerie (galerie.html) l'appelle
 *  pour construire le carrousel.
 *
 *  POURQUOI CE FICHIER ?
 *  L'hébergeur LWS interdit la « liste de dossier » automatique (erreur 403).
 *  Ce script contourne cette limite : vous continuez à SIMPLEMENT DÉPOSER vos
 *  photos dans le dossier « realisations » — elles s'affichent toutes seules.
 *  Il n'y a JAMAIS rien à modifier ici.
 *  ───────────────────────────────────────────────────────────────────────── */

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, max-age=0');

$dir        = __DIR__ . '/realisations';
$extensions = array('jpg', 'jpeg', 'png', 'webp', 'gif', 'avif');
$files      = array();

if (is_dir($dir)) {
    foreach (scandir($dir) as $name) {
        if ($name === '.' || $name === '..') continue;
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (in_array($ext, $extensions, true)) {
            $files[] = $name;
        }
    }
}

// Tri par nom de fichier (ordre naturel : 01_, 02_, 10_ …)
natcasesort($files);
$files = array_values($files);

echo json_encode($files, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
