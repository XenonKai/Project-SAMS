let currentRole = '';
function selectRole(role){
  currentRole = role;
  document.getElementById('roleInput').value = role;
  document.getElementById('roleLabel').innerText = role.toUpperCase();
  document.querySelectorAll('.role-card').forEach(c=>c.classList.remove('active'));
  document.getElementById('card-'+role).classList.add('active');
  document.getElementById('signupForm').classList.remove('hidden');
  document.getElementById('studentFields').classList.add('hidden');
  document.getElementById('facultyFields').classList.add('hidden');
  document.getElementById('adminFields').classList.add('hidden');
  if(role=='student') document.getElementById('studentFields').classList.remove('hidden');
  if(role=='faculty') document.getElementById('facultyFields').classList.remove('hidden');
  if(role=='admin') document.getElementById('adminFields').classList.remove('hidden');
}
function showSignup(){ document.getElementById('signupForm').classList.remove('hidden'); document.getElementById('loginForm').classList.add('hidden'); }
function showLogin(){ document.getElementById('loginForm').classList.remove('hidden'); document.getElementById('signupForm').classList.add('hidden'); }
function togglePass(){ let p=document.getElementById('pass'); p.type = (p.type==='password')?'text':'password'; }