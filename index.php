<?php
@include_once('base32.php');

function wget($url, $filename, $post_data = array()) {
    $settings = getSettings();
    $call = array(
        'wget',
        '-q',
        '--no-check-certificate',
        '--save-cookies ' . $settings['user']['download'] . '/cookies.txt',
        '--load-cookies ' . $settings['user']['download'] . '/cookies.txt',
        '--keep-session-cookies'
    );

    if (!is_dir($settings['user']['download'])) {
        mkdir($settings['user']['download'], 0777, true);
    }
    $call[] = '-O ' . $settings['user']['download'] . '/' . $filename;

    $post = array();
    foreach ($post_data as $key => $value) {
        $post[] = $key . '=' . $value;
    }
    if (!empty($post)) {
        $call[] = '--post-data "' . implode('&', $post) . '"';
    }

    $call[] = $settings['speedport']['url'] . $url;
    $call = implode(' ', $call); 
    echo $call . "\n";
    @exec($call, $output, $retval);
}

function getSettings() {
    return array_merge(
        parse_ini_file('settings.ini', true),
        parse_ini_file('sph.ini', true)
    );
}

function getAllSites() {
    $settings = getSettings();
    foreach ($settings['json'] as $name => $url) {
        wget($url, $name);
    }
}

function login() {
    $settings = getSettings();
    wget($settings['json']['login'], 'login_data', array(
        'password' => $settings['user']['password_encrypted'],
        'showpw' => 0,
        'csrf_token' => 'nulltoken',
    ));
}


login();
getAllSites();

echo base64_decode(file_get_contents('download/enc_inetip'));
