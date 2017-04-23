<?php
@include_once('sph.php');
$sph = new SPH();
$sph->login();


function getAllSites($sph) {
    $settings = $sph->getSettings();
    foreach ($settings['json'] as $name => $url) {
        $sph->wget($url, $name);
    }
}
getAllSites($sph);

