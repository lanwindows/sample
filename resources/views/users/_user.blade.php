<li>
    <img src="{{ $user->gravatar() }}" alt="{{ $user->name }}" class="gravatar"/>
    <a href="{{ route('users.show', $user->id )}}" class="username">{{ $user->name }}</a>

    @can('destroy', $user)
    {{--
        <!--Laravel 授权策略提供了 @can Blade 命令，允许我们在 Blade 模板中做授权判断。利用 @can 指令，只有管理员才能看到的删除用户按钮。-->
    --}}
      <form action="{{ route('users.destroy', $user->id) }}" method="post">
        {{ csrf_field() }}
        {{ method_field('DELETE') }}
        <button type="submit" class="btn btn-sm btn-danger delete-btn">删除</button>
      </form>
    @endcan
</li>
