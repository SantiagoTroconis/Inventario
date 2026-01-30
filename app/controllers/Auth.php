<?php


class Auth extends Controller
{
    private $userModel;

    public function index()
    {
        if (isset($_SESSION['usuario'])) {
            header('Location: ' . URL_BASE . '/home.php');
            exit();
        }

        $this->view('auth/login');
        exit();
    }

    public function login()
    {
        $this->userModel = new UserModel();

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            if (!empty($username) && !empty($password)) {
                // Buscar usuario en la base de datos
                $user = $this->userModel->login($username, $password);

                if ($user) {
                    // Verificar si el usuario está activo
                    if ($user->status == 1) {
                        // Guardar información en sesión
                        $_SESSION['usuario_id'] = $user->usuario_id;
                        $_SESSION['usuario'] = $user->nombre;
                        $_SESSION['tipo_usuario'] = $user->tipo_usuario;
                        $_SESSION['correo'] = $user->correo;

                        // Redirigir al home después del login exitoso
                        header('Location: ' . URL_BASE . '/home.php');
                        exit();
                    } else {
                        $this->view('auth/login', ['error' => 'Usuario inactivo. Contacte al administrador.']);
                    }
                } else {
                    $this->view('auth/login', ['error' => 'Usuario o contraseña incorrectos.']);
                }
            } else {
                $this->view('auth/login', ['error' => 'Por favor ingrese usuario y contraseña.']);
            }
        }
    }

    public function logout()
    {
        session_destroy();
        header('Location: ' . URL_BASE . '/auth.php');
        exit();
    }
}
