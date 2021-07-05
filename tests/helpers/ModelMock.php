<?php





class ModelMock
{
    public $id=1;

    public function find(){
        return $this;
    }

}