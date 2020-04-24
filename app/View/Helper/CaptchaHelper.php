<?php
//session_start();
class CaptchaHelper extends AppHelper {
    public $helpers = array('Session');
    private $_values;
    private $_ext;
    private $_path;
    private $_size;

    public function init($images, $ext = 'jpg', $path_images, $sW, $sH) {
        $this->_values = $images;
        $this->_ext = $ext;
        $this->_path = $path_images;
        $this->_size = array('w' => $sW, 'h' => $sH);
    }

    public function draw() {
        $session = $this->_View->getVar('session');
        $rand = mt_rand(0, (sizeof($this->_values) - 1));
        shuffle($this->_values);
        
        $s3Capcha = __('captcha_disclaimer',true) . __($this->_values[$rand],true) .'<div>';
        for ($i = 0; $i < sizeof($this->_values); $i++) {
            $value2[$i] = mt_rand();
            $s3Capcha .= '<div class="option"><span>' . $this->_values[$i] . ' <input type="radio" name="s3capcha" value="' . $value2[$i] . '"></span><div style="background: url(../' . $this->_path . $this->_values[$i] . '.' . $this->_ext . ') bottom left no-repeat; width:' . $this->_size['w'] . 'px; height:' . $this->_size['h'] . 'px;cursor:pointer;display:none;" class="img"></div></div>';
        }
        $s3Capcha.='</div>';
        $session->write('s3capcha', $value2[$rand]);
        //$_SESSION['s3capcha'] = $value2[$rand];
        //CakeSession::write('s3capcha', $value2[$rand]);
        //$this->Session->write('s3capcha', $value2[$rand]);
        echo $s3Capcha;
    }
}