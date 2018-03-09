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
use \RedBeanPHP\R as R;

class Prof {
    //put your code here
    
    
    /**
     * xhprof_flags
     * @var type 
     */
    protected $xhprof_flags ;
    
    
    /**
     * xhprof_options
     * @var type 
     */
    protected $xhprof_options ;
    
    
    /**
     *
     * @var type 
     */
    protected $project = 'default';




    /**
     * dsn
     * @var string
     */
    private $dsn;
    
    /**
     * user
     * @var string 
     */
    private  $user;
    
    /**
     * pwd
     * @var string 
     */
    private  $pwd;
            
    
    
    /**
     * 
     * @param type $dsn
     * @param type $user
     * @param type $pwd
     * @return $this
     * @throws Exception
     */
    function __construct($dsn, $user, $pwd) {
        if(!extension_loaded('xhprof')){
            throw new Exception('please install xhprof：http://php.net/manual/zh/book.xhprof.php');
        }
        $this->registerShutDown();
        $this->xhprof_flags = XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU ;
        $this->dsn = $dsn;
        $this->user = $user;
        $this->pwd = $pwd;
        return $this;
      
    }
    
    /**
     * 
     * @param type $xhprof_flags
     * @param type $xhprof_options
     * @return $this
     */
    public function setXhprofParams($xhprof_flags, $xhprof_options){
        $this->xhprof_flags = $xhprof_flags;
        $this->xhprof_options = $xhprof_options;
        return $this;
    }
    
    
    
    /**
     * 
     * @param type $project
     * @return $this
     */
    public function setProjectName($project){
        $this->project = $project;
        return $this;
    }
    /**
     * 
     * @param type $flags
     * @param type $options
     */
    public function run(){
        $this->beginTime = microtime(true);
        xhprof_enable($this->xhprof_flags, $this->xhprof_options);
    }
    


    protected  function save($data){
             $request = new Request();
             R::setup($this->dsn, $this->user, $this->pwd);
             $xhprof = R::dispense('xhprof');
             $xhprof->path = $request->getPathInfo();
             $xhprof->url = $request->getRequestUri();
             $xhprof->post_data = serialize($request->getPostData());
             $xhprof->expended_time = microtime(true) - $this->beginTime;
             $xhprof->project = $this->project;
             $xhprof->create_at = time();
             $id = R::store($xhprof);
             $xhprof_detail = R::dispense('xdetail');
             $xhprof_detail->xid = $id;
             $xhprof_detail->content = serialize($data);
             $xhprof_detail->create_at = $xhprof->create_at;
             R::store($xhprof_detail);
    }


    /**
     * 注册程序结束的方法
     */
    protected function registerShutDown(){
        register_shutdown_function(function(){
             //fastcgi_finish_request();//冲刷(flush)所有响应的数据给客户端 http://php.net/manual/zh/function.fastcgi-finish-request.php
             $this->save(xhprof_disable());
        });
    }
}
