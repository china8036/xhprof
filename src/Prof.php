<?php
namespace Qqes\Xhprof;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Prof
 *
 * @author wang
 */
class Prof {
    //put your code here
    
    
    
    
    
    function __construct($flags, $options = array()) {
        if(!extension_loaded('xhprof')){
            throw new Exception('please install xhprof：http://php.net/manual/zh/book.xhprof.php');
        }
        $this->registerShutDown();
        $this->init($flags, $options);
    }
    
    
    /**
     * 
     * @param type $flags
     * @param type $options
     */
    protected function init($flags, $options){
        xhprof_enable($flags, $options);
    }




    /**
     * 注册程序结束的方法
     */
    protected function registerShutDown(){
        register_shutdown_function(function(){
             //fastcgi_finish_request();//冲刷(flush)所有响应的数据给客户端 http://php.net/manual/zh/function.fastcgi-finish-request.php
             $data = xhprof_disable();
             echo '<pre>';
             var_dump($data);
             echo '</pre>';
        });
    }
}
