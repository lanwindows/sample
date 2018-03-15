<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth',[
            'except' => ['show', 'create', 'store', 'index']
        ]);
        /*
        __construct 是 PHP 的构造器方法，当一个类对象被创建之前该方法将会被调用。在 __construct 方法中调用了 middleware 方法，该方法接收两个参数，第一个为中间件的名称，第二个为要进行过滤的动作。通过 except 方法来设定指定动作不使用 Auth 中间件进行过滤，意为 —— 除了此处指定的动作以外，所有其他动作都必须登录用户才能访问，类似于黑名单的过滤机制。相反的还有 only 白名单方法，将只过滤指定动作。提倡在控制器 Auth 中间件使用中，首选 except 方法，这样的话，当新增一个控制器方法时，默认是安全的，此为最佳实践。
        */

        $this->middleware('guest', [
            'only' => ['create']
        ]);
        /*
        使用 Auth 中间件提供的 guest 选项，用于指定一些只允许未登录用户访问的动作.只让未登录用户访问注册页面.
        */
    }

    public function index()
    {
        $users = User::paginate(10);//使用 paginate 方法来指定每页生成的数据数量为 10 条,分页
        return view('users.index', compact('users'));
    }


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

    public function edit(User $user)
    /*
    edit 动作主要做了以下几个操作：利用了 Laravel 的『隐性路由模型绑定』功能，直接读取对应 ID 的用户实例 $user，未找到则报错；将查找到的用户实例 $user 与编辑视图进行绑定；
    */
    {
        $this->authorize('update', $user);
        /*
        默认的 App\Http\Controllers\Controller 类包含了 Laravel 的 AuthorizesRequests trait。此 trait 提供了 authorize 方法，它可以被用于快速授权一个指定的行为，当无权限运行该行为时会抛出 HttpException。authorize 方法接收两个参数，第一个为授权策略的名称，第二个为进行授权验证的数据。

        这里 update 是指授权类里定义的 update 授权方法(见UserPolicy.php)，$user 对应传参 update 授权方法的第二个参数。正如定义 update 授权方法时候提起的，调用时，默认情况下，不需要传递第一个参数，也就是当前登录用户至该方法内，因为框架会自动加载当前登录用户。
        */
        return view('users.edit', compact('user'));//将用户数据与视图进行绑定
    }

    public function update(User $user, Request $request)
    /*
    定义的 update 方法接收两个参数，第一个为自动解析用户 id 对应的用户实例对象，第二个则为更新用户表单的输入数据。
    */
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);//对用户提交的信息进行验证

        $this->authorize('update', $user);//使用 authorize 方法来验证用户授权策略

        $data = [];
        $data['name'] = $request->name;
        if($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        //对传入的 password 进行判断，当其值不为空时才将其赋值给 data，避免将空白密码保存到数据库中.调用 update 方法对用户对象进行更新.

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user->id);//用户个人资料更新成功后，将用户重定向到个人页面
    }

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }
}
