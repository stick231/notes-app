<?php
namespace Repository;

Use Entities\Database;
use Entities\User;

class UserRepository{
    private $pdo;

    public function __construct(Database $database) {
        $this->pdo = $database::getInstance()->getConnection();
    }

    public function checkUser(User $user)
    {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$user->getUsername()]);
            $existingUser = $stmt->fetch();
        
            if ($existingUser) {
                return false;
            }
            return true;
        }
        catch(\PDOException $e){
            "Ошибка при проверке пользователя: " . $e->getMessage();
        }
    }

    public function register(User $user) 
    {
        try{
            if($this->checkUser($user)){
                $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
                $stmt->execute([$user->getUsername(), password_hash($user->getPassword(), PASSWORD_DEFAULT)]);

                $userId = $this->pdo->lastInsertId();
                setcookie("user_id", $userId, time() + 3600 * 24 * 30, "/"); 
                setcookie("auth_user_id", $userId, time() + 7200, "/");
                setcookie("register", 'true', time() + 3600 * 24 * 30, "/");
                setcookie('just_register', $user->getUsername(), time() + 7200, '/'); 

                return true;
            } 
            return false;
        }
        catch(\PDOException $e){
            "Ошибка при регистрации пользователя: " . $e->getMessage();
        }
    }

    public function authenticate(User $user)
    {
        try{
            $query = "SELECT * FROM users WHERE username = ?";
            $stmt = $this->pdo->prepare($query);
            $username = $user->getUsername();
            $stmt->bindParam(1, $username, \PDO::PARAM_STR);
            $stmt->execute();
            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                foreach($result as $row) {
                if (password_verify($user->getPassword(), $row["password"])) {
                    $userId = $row['id'];
                    setcookie("user_id", $userId, time() + 3600 * 24 * 30, "/");
                    setcookie("auth_user_id", $userId, time() + 7200, "/");
                    setcookie("login", $user->getUsername(), time() + 7200, "/");
                    setcookie("register", 'true', time() + 3600 * 24 * 30, "/");
                } else {
                    $warning = "Неверный логин или пароль";
                    return $warning;
                }
            }
        }
            else {
                $warning = "Пользователь не найден";
                return $warning;
            }
        }
        catch(\PDOException $e){
            "Ошибка при аунтентификации пользователя: " . $e->getMessage();
        }
    }

    public function findByUsername($username) 
    {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $userData = $stmt->fetch();
    
            if ($userData) {
                return new User($userData['username'], $userData['password']);
            }
    
            return null;
        }
        catch(\PDOException $e){
            echo "Ошибка поиске пользователя: " . $e->getMessage();
            return false;
        }
    }
}