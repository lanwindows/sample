<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;

class UsersController extends Controller
{
    public function create()
    {
        return view('users.create');
    }

    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    public function store(Request $request)
    {
        //验证输入信息 字段 => '规则'
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        Auth::login($user);//用户注册成功后自动登录
        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
        /*
        用户注册成功后，在页面顶部位置显示注册成功的提示信息。由于 HTTP 协议是无状态的，所以 Laravel 提供了一种用于临时保存用户数据的方法 - 会话（Session），并附带支持多种会话后端驱动，可通过统一的 API 进行使用。
        我们可以使用 session() 方法来访问会话实例。而当我们想存入一条缓存的数据，
        让它只在下一次的请求内有效时，则可以使用 flash 方法。flash 方法接收两个参数，
        第一个为会话的键，第二个为会话的值
        */
        return redirect()->route('users.show', [$user]);

        /*
        用户模型 User::create() 创建成功后会返回一个用户对象，并包含新注册用户的所有
        信息。我们将新注册用户的所有信息赋值给变量 $user，并通过路由跳转来进行数据绑定。
        注意这里是一个『约定优于配置』的体现，此时 $user 是 User 模型对象的实例。
        route() 方法会自动获取 Model 的主键，也就是数据表 users 的主键 id，以上代码
        等同于：redirect()->route('users.show', [$user->id]);
        */
    }
}
