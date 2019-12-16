<?php

namespace App\Http\Controllers\BackendManager;

use App\Http\Controllers\Controller;
use App\Http\Requests\BackEnd\fileRequest;
use App\Http\Requests\BackEnd\project_idRequest;
use App\Http\Requests\BackEnd\SettingFileRequest;
use App\Model\Annex;
use App\Model\Project;
use App\Utils\Logs;
use Illuminate\Support\Facades\Storage;

class SettingFileController extends Controller
{
    public function getConfigurationFileSetting(project_idRequest $request)
    {
        $project_id = $request->get("project_id");

        try {
            $annex = Annex::where("project_id", $project_id)->get(["path"]);
            $annex = json_decode($annex, true);
            $project = Project::find($project_id);

            if ($annex != null && $project != null) {

                return \response()->json([
                    "code" => 200,
                    "msg" => "配置文件获取成功",
                    "data" => [
                        "github_address" => $project["pre_url"],
                        "Storage_url" => $annex[0]["path"]
                    ]
                ]);
            } else {
                return \response()->json([
                    "code" => 100,
                    "msg" => "获取配置文件设置异常",
                    "data" => null
                ]);
            }
        } catch (\Exception $e) {
            Logs::logError("添加配置文件异常," . "Exception:".$e->getMessage());
            return \response()->json([
                "code" => 500,
                "msg" => "获取配置文件设置异常",
                "data" => null
            ]);
        }
    }

    public function updateConfigurationFileSetting(SettingFileRequest $request)
    {
        $path = $request->get("project_sql");
        $project_id = $request->get("project_id");
        $github_address = $request->get("github_address");

        try {

            $annex = Annex::where("project_id", $project_id)->get();
            $annex[0]["path"] = $path;

            $updated = json_decode($annex[0], true);

            $annex[0]->id = $updated["id"];
            $annex[0]->project_id = $updated["project_id"];
            $annex[0]->path = $updated["path"];
            $annex[0]->type = $updated["type"];

            $project = Project::find($project_id);
            $project->pre_url = $github_address;

            if ($annex[0]->save() && $project->save()) {
                return \response()->json([
                    "code" => 200,
                    "msg" => "更新配置文件成功",
                    "data" => null
                ]);
            } else {
                return \response()->json([
                    "code" => 100,
                    "msg" => "更新配置文件失败",
                    "data" => null
                ]);
            }

        } catch (\Exception $exception) {
            Logs::logError("更新配置文件异常," ."Exception:".$exception->getMessage());
            return \response()->json([
                "code" => 500,
                "msg" => "更新配置文件异常",
                "data" => null
            ]);
        }
    }

    public function addConfigurationFileSetting(SettingFileRequest $request)
    {
        $path = $request->get("project_sql");
        $project_id = $request->get("project_id");
        $github_address = $request->get("github_address");

        try {

            $annex = new Annex();
            $annex->project_id = $project_id;
            $annex->path = $path;
            $annex->type = 0;

            $project = Project::find($project_id);
            $project->pre_url = $github_address;

            if ($project->save() && $annex->save()) {

                return \response()->json([
                    "code" => 200,
                    "msg" => "添加配置文件成功",
                    "data" => null
                ]);
            } else {
                return \response()->json([
                    "code" => 100,
                    "msg" => "添加配置文件失败",
                    "data" => null
                ]);
            }
        } catch (\Exception $e) {
            Logs::logError("添加配置文件异常," . "Exception:".$e->getMessage());
            return \response()->json([
                "code" => 500,
                "msg" => "添加配置文件异常",
                "data" => null
            ]);
        }
    }

    public function uploadConfigurationFile(fileRequest $request)
    {

        try {
            $fileCharater = $request->file('file');

            if ($fileCharater->isValid()) {
                //检查文件是否有效
                //获取文件的扩展名
                $ext = $fileCharater->getClientOriginalExtension();
                //判断后缀是否为sql结尾
                if ($ext == 'sql') {
                    //获取文件的绝对路径
                    $path = $fileCharater->getRealPath();

                    //定义文件名,更具时间命名防止文件覆盖
                    $filename = date('Y-m-d-h-i-s-'.rand(0,99999)) . '.' . $ext;

                    //存储文件
                    if (Storage::disk('public')->put($filename, file_get_contents($path))) {
                        return \response()->json([
                            "code" => 200,
                            "msg" => "sql文件上传成功",
                            "data" => [
                                "Storage_url" => Storage::url("public/" . $filename)
                            ]
                        ]);
                    };
                } else {

                    return \response()->json([
                        "code" => 100,
                        "msg" => "文件类型错误",
                        "data" => null
                    ]);
                }
            }
        }catch (\Exception $exception){
            Logs::logError("sql文件上传异常," . "Exception:".$exception->getMessage());
            return \response()->json([
                "code" => 100,
                "msg" => "无效文件",
                "data" => null
            ]);
        }
    }
}
