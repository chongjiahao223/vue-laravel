<?php
namespace App\Http\Services\Admin;

use App\Http\Services\Base;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Store extends Base
{
    protected static $ruler = [
        'name'  => '',
        'phone'   => 'required|numeric|regex:/^1[3456789][0-9]{9}$/|unique:admins',
        'password'  => 'required|min:6|max:14',
        'password_confirmation'  => 'required|same:password'
    ];
    protected static $message = [
        'name.required' => '请输入用户名',
        'name.max' => '用户名最长14位',
        'name.unique' => '用户名重复',
        'phone.required' => '请输入手机号',
        'phone.numeric' => '手机号输入类型错误',
        'phone.regex' => '手机号输入格式错误',
        'phone.unique' => '手机号重复',
        'password.required' => '请输入密码',
        'password.min' => '请输入6~14位密码',
        'password.max' => '请输入6~14位密码',
        'password_confirmation.required' => '请输入确认密码',
        'password_confirmation.same' => '两次密码不一致',
    ];
    public static function dryRun(Request $request)
    {
        $verify = static::verify($request);
        if ($verify) return static::response([], $verify, 500);
        try {
            Admin::create($request->only('phone', 'name', 'password'));
            return static::response([], '添加成功', 200);
        } catch (\Exception $e) {
            return static::response([], '添加失败', 500);
        }
    }

    /**
     * 验证
     * @param Request $request
     * @return mixed|void
     */
    private static function verify(Request $request)
    {
        static::$ruler['name'] = [
            'required',
            'max:14',
            Rule::unique('admins')->where(function ($query) {
                return $query->whereNull('deleted_at');
            })
        ];
        static::$ruler['phone'] = [
            'required',
            'numeric',
            'regex:/^1[3456789][0-9]{9}$/',
            Rule::unique('admins')->where(function ($query) {
                return $query->whereNull('deleted_at');
            })
        ];
        $validator = Validator::make($request->all(), static::$ruler, static::$message);
        if ($validator->fails()) {
            $errors = $validator->errors()->getMessages();
            foreach($errors as $key=>$value)
            {
                return $value[0];
            }
        }
        return ;
    }
}
