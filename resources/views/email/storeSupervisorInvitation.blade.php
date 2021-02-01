<p>You are receiving this email because store invited you to become their supervisor.</p>

<p>If you did not have a account with our app, you can click the button below to register one.</p>
<a href="{{ route('store.supervisor.register', ['token' => $token]) }}" class="btn btn-info">Register</a>

<p>Or if you already have a account with our app, you can click the button below to login.</p>
<a href="{{ route('store.supervisor.login', ['token' => $token]) }} " class="btn btn-info">Login</a>

<p>After you successfully register or login, you can login with your account in our app as a supervisor of store.</p>
<p>If you did not invite by the store, no futher action is require.</p>
<p>Token = </p><?php echo $token ?>

<p>Regard,</p>
<p>黑白點-線上點餐系統</p>