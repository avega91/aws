<?php
class IndexController extends AppController {
    public function beforeFilter() {
        parent::beforeFilter();
        $this->uses[] = 'User';
    }

    public function view(){
        $users = $this->User->find('all');
        $this->set('users', $users);
    }
}