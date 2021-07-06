<?php





class SessionMock
{
    /**
     * @var array
     */
    protected $session=array();

    /**
     * @param $key
     * @return \Illuminate\Support\Collection|null
     */
    public function get($key){
        return key_exists($key,$this->session)?collect($this->session[$key]):null;
    }

    /**
     * @param array $value
     * @return $this
     */
    public function put(array $value){
        $this->session=array_merge($this->session,$value);
        return $this;
    }

}