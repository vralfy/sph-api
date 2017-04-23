<?php
class SPH {

    protected $settings;

    protected $challengev;

    public function __construct() {
        $this->loadSettings();
    }

    public function loadSettings() {
        $this->settings =  array_merge(
            parse_ini_file('settings.ini', true),
            parse_ini_file('sph.ini', true)
        );
    }

    public function getSettings() {
        return $this->settings;
    }

    public function wget($url, $filename, $post_data = array()) {
        $call = array(
            'wget',
            '-q',
            '--no-check-certificate',
            '--save-cookies ' . $this->settings['user']['download'] . '/cookies.txt',
            '--load-cookies ' . $this->settings['user']['download'] . '/cookies.txt',
            '--keep-session-cookies'
        );

        if (!is_dir($this->settings['user']['download'])) {
            mkdir($this->settings['user']['download'], 0777, true);
        }
        $call[] = '-O ' . $this->settings['user']['download'] . '/' . $filename;

        $post = array();
        foreach ($post_data as $key => $value) {
            $post[] = $key . '=' . $value;
        }
        if (!empty($post)) {
            $call[] = '--post-data "' . implode('&', $post) . '"';
        }

        $call[] = $this->settings['speedport']['url'] . $url;
        $call = implode(' ', $call); 
        echo $call . "\n";
        @exec($call, $output, $retval);
    }

    function getContent($file) {
        return file_get_contents($this->settings['user']['download'] . '/' . $file);
    }

    function login() {
        $encrypted_password = null;
        if (isset($this->settings['user']['password'])) {
            $this->wget($this->settings['json']['login'], 'token', array(
                'challengev' => 'null',
                'showpw' => 0,
                'csrf_token' => 'nulltoken',
            ));

            $tokendata = json_decode($this->getContent('token'), true);
            foreach ($tokendata as $obj) {
                if ($obj['varid'] === 'challengev') {
                    $this->challengev = $obj['varvalue'];
                }
            }
            $encrypted_password = $this->challengev . ':' . $this->settings['user']['password'];
            $encrypted_password = hash($this->settings['pbkdf2']['algorithm'], $encrypted_password);
        } else if (isset($this->settings['user']['password_encrypted']) {
            $encrypted_password = $this->settings['user']['password_encrypted'];
        }


        $this->wget($this->settings['json']['login'], 'login_data', array(
            'password' => $encrypted_password,
            'showpw' => 0,
            'csrf_token' => 'nulltoken',
        ));
    }
}
?>
