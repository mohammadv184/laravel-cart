<?php





class SessionMock
{
    protected $session=array();

    public function get($key){
        return key_exists($key,$this->session)?collect($this->session[$key]):null;
    }
    public function put(array $value){
        $this->session=array_merge($this->session,$value);
        return $this;
    }

}