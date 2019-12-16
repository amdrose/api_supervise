<?php

namespace App\Model;

use App\Utils\Logs;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ProjectModule extends Model
{
    //定义模型关联的数据表
    protected $table = 'project_modules';
    //定义主键
    protected $primaryKey = 'id';
    //定义禁止操作时间
    public $timestamps = true;
    protected $guarded = [];

    //获取所有的模型名(单用)
    public static function findModules($project_id){
        try{
            $result = self::where('project_id',$project_id)
                ->select('id','project_id','modules_name','class_name','full_class_name','utility')
                ->get();
            return $result;
        } catch (Exception $e){
            Logs::logError('获取所有模型信息失败!', [$e->getMessage()]);
            return response()->fail(100, '获取所有模型信息失败，请重试!', null);
        }
    }
    //1.模块设置
    //查询模块
    public static function selectModuleMethod()
    {
        try {
            return $module = ProjectModule::paginate(env('PAGE_NUM'), ['modules_name', 'class_name', 'full_class_name']);
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('查询模块失败!', [$e->getMessage()]);
            return null;
        }

    }

    //新增模块
    public static function addModuleMethod($input)
    {
        try {
            //开启事务
            DB::beginTransaction();
            $rs = ProjectModule::create($input);
            DB::commit();
            return $rs;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('新增模块信息失败!', [$e->getMessage()]);
            DB::rollback();
            return null;
        }
    }

    //修改模块
    public static function editModuleMethod($input, $m_id)
    {
        try {
            //开启事务
            DB::beginTransaction();
            $rs = ProjectModule::where('id', $m_id)->update($input);
            DB::commit();
            return $rs;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('修改模块信息失败!', [$e->getMessage()]);
            DB::rollback();
            return null;
        }
    }

    //删除模块
    public static function deModuleMethod($m_id)
    {
        try {
            //开启事务
            DB::beginTransaction();
            $rs = ProjectModule::where('id', $m_id)->delete();
            DB::commit();
            return $rs;
        } catch (\Exception $e) {
            \App\Utils\Logs::logError('删除模块信息失败!', [$e->getMessage()]);
            DB::rollback();
            return null;
        }
    }

}
