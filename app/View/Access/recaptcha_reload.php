<?php
$this->Captcha->init(array('battery','camera','car','flag','rocket'),'png','img/captcha/',35,30); 
echo $this->Captcha->draw();