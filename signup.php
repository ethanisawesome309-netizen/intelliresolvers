<?php
session_start();
require_once 'config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $register_error = "All fields are required!";
    } elseif ($password !== $confirm_password) {
        $register_error = "Passwords do not match!";
    } else {
        try {
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $register_error = "Email is already registered!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt_insert = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $stmt_insert->bind_param("sss", $name, $email, $hashed_password);
                $stmt_insert->execute();

                $_SESSION['user_id'] = $stmt_insert->insert_id;
                $_SESSION['user_name'] = $name;
                header("Location: dashboard.php");
                exit();
            }

            $stmt->close();
            if (isset($stmt_insert)) $stmt_insert->close();
        } catch (Exception $e) {
            $register_error = "Database Error: " . $e->getMessage();
        }
    }

    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign Up - INTELLI RESOLVERS</title>
<script src="https://kit.fontawesome.com/9f925d73ef.js" crossorigin="anonymous"></script>
<link href="https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@100..900&display=swap" rel="stylesheet">

<style>
* { 
  box-sizing: border-box; 
  margin: 0; 
  padding: 0; 
  font-family: 'Kumbh Sans', sans-serif; 
}

body { 
  background: #141414; 
  color: #fff; 
  display: flex; 
  flex-direction: column; 
  min-height: 100vh; 
}

/* NAVBAR */
.navbar { 
      background: #131313; 
      height: 80px; 
      display: flex; 
      justify-content: center; 
      align-items: center; 
      font-size: 1.2rem; 
      position: sticky; 
      top: 0; z-index: 999; 
    }

    .navbar__container { 
      display: flex; 
      justify-content: space-between;
      width: 100%; 
      max-width: 1300px; 
      padding: 0 50px; 
      align-items: center; 
    }

    #navbar__logo { 
      background: linear-gradient(to top, #ff0844 0%, #ffb199 100%); 
      -webkit-background-clip: text; 
      -webkit-text-fill-color: 
      transparent; font-size: 2rem; 
      display: flex; 
      align-items: center; 
      cursor: pointer; 
      text-decoration: none; 
    }

    .fa-gem { 
      margin-right: 0.5rem; 
    }

    .navbar__menu { 
      display: flex; 
      align-items: center; 
      list-style: none; 
    }

    .navbar__item { 
      height: 80px; 
    }
    .navbar__links { 
      color: #fff; 
      text-decoration: none; 
      padding: 0 1rem; 
      display: flex; 
      align-items: center; 
      height: 100%; 
    }

    .navbar__links:hover { 
      color: #f77062; 
      transition: 0.3s ease; 
    }

    .navbar__btn .button { 
      text-decoration: none; 
      background: linear-gradient(to right, #f77062, #fe5196); 
      color: #fff; 
      padding: 10px 20px; 
      border-radius: 4px; 
      transition: 0.3s; }
    .navbar__btn .button:hover { 
      background: #4837ff; 
    }

/* HAMBURGER */
/* HAMBURGER */
    .navbar__toggle { 
      display: none; 
      flex-direction: column; 
      cursor: pointer; 
    }

    .bar { 
      height: 3px; 
      width: 25px; 
      margin: 5px auto; 
      background: #fff; 
      transition: all 0.3s ease; 
    }

    @media screen and (max-width: 960px) {
      .navbar__toggle { 
        display: flex; 
      }

      .navbar__menu { 
        flex-direction: column; 
        position: absolute; 
        top: -1000px; 
        left: 0; 
        width: 100%; 
        opacity: 0; 
        background: #131313; 
        transition: all 0.5s ease; 
        z-index: -1; 
      }

      .navbar__menu.active { 
        top: 100%; 
        opacity: 1; 
        z-index: 99; 
        font-size: 1.6rem; 
        height: 50vh; 
      }

      #mobile-menu.is-active .bar:nth-child(2) { opacity: 0; }
      #mobile-menu.is-active .bar:nth-child(1) { transform: translateY(8px) rotate(45deg); }
      #mobile-menu.is-active .bar:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
    }
}

/* SIGNUP FORM */
.signup-container { 
  flex: 1; 
  display: flex; 
  justify-content: center; 
  align-items: center; 
  padding: 5rem 1rem; 
}

.signup-card { 
  background: #1f1f1f; 
  padding: 3rem 2rem; 
  border-radius: 8px; 
  box-shadow: 0 4px 12px rgba(0,0,0,0.5); 
  width: 100%; 
  max-width: 400px; 
  text-align: center; 
}

.signup-card h1 { 
  font-size: 2.5rem; 
  margin-bottom: 1.5rem; 
  background: linear-gradient(to top, #f77062 0%, #fe5196 100%); 
  -webkit-background-clip: text; 
  -webkit-text-fill-color: transparent; 
}

.signup-card input[type="text"], .signup-card input[type="email"], .signup-card input[type="password"] { 
  width: 100%; 
  padding: 12px 15px; 
  margin: 10px 0;
  border-radius: 4px; 
  border: none; 
  outline: none; 
  background: #2a2a2a; 
  color: #fff; 
}

.signup-card input::placeholder { 
  color: #aaa; 
}
.signup-card button { 
  width: 100%; 
  padding: 12px; 
  margin-top: 1rem; 
  border: none; 
  border-radius: 4px; 
  background: linear-gradient(to right, #e53935, #8e0e00); 
  color: #fff; 
  font-size: 1rem; 
  cursor: pointer; 
  transition: all 0.3s; 
}

.signup-card button:hover { 
  background: #4837ff; 
}

.signup-card .signin-link { 
  margin-top: 1rem; 
  font-size: 0.9rem; 
  color: #aaa; 
  display: block; 
  text-decoration: none; 
}

.signup-card .signin-link:hover { 
  color: #f77062; 
}

.error-message { 
  color: #ff4b5c; 
  margin-bottom: 10px; 
}
.success-message { 
  color: #4caf50; 
  margin-bottom: 10px; 
}

/* FOOTER */
.footer__container { 
      background-color: #141414; 
      padding: 5rem 0; 
      display: flex; 
      flex-direction: column; 
      align-items: center; 
    }

    #footer__logo { 
      color: #fff; 
      text-decoration: none; 
      font-size: 2rem; 
      display: flex; 
      align-items: center; 
      margin-bottom: 1.5rem; 
    }

    #footer__logo i { 
      color: #f77062; 
      margin-right: 0.5rem; 
    }

    .footer__links { 
      display: flex; 
      justify-content: center; 
      flex-wrap: wrap; 
      gap: 3rem; 
      margin-bottom: 2rem; 
    }

    .footer__link--items { 
      display: flex; 
      flex-direction: column; 
    }
    .footer__link--items h3 { 
      color: #f77062; 
      margin-bottom: 1rem; 
    }
    .footer__link--items a { 
      color: #fff; 
      text-decoration: none; 
      margin-bottom: 0.5rem;
    }
    .footer__link--items a:hover { 
      color: #f77062; 
    }

    .website__rights { 
      color: #777; 
      font-size: 0.8rem; 
      text-align: center; 
    }
    .social__icons a { 
      color: #fff; 
      margin: 0 10px; 
      font-size: 1.2rem; 
      transition: 0.3s; 
    }
    .social__icons a:hover { 
      color: #f77062; 
    }
@media screen and (max-width: 768px) { .signup-card { width: 90%; padding: 2rem; } }
    </style>
  </head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
<div class="navbar__container">
<a href="index.html" id="navbar__logo"><i class="fas fa-gem"></i>INTELLI RESOLVERS</a>
<div class="navbar__toggle" id="mobile-menu">
  <span class="bar"></span>
  <span class="bar"></span>
  <span class="bar"></span>
</div>
<ul class="navbar__menu">
  <li class="navbar__item"><a href="index.html" class="navbar__links">Home</a></li>
  <li class="navbar__item"><a href="services.html" class="navbar__links">Services</a></li>
  <li class="navbar__item"><a href="team.html" class="navbar__links">Team</a></li>
  <li class="navbar__btn"><a href="signinpage.php" class="button">Sign In</a></li>
</ul>
</div>
</nav>

<!-- SIGNUP FORM -->
<div class="signup-container">
<div class="signup-card">
<h1>Create Account</h1>

<?php if (!empty($register_error)) echo "<p class='error-message'>$register_error</p>"; ?>

<form action="signup.php" method="POST">
  <input type="text" name="name" placeholder="Full Name" required>
  <input type="email" name="email" placeholder="Email" required>
  <input type="password" name="password" placeholder="Password" required>
  <input type="password" name="confirm_password" placeholder="Confirm Password" required>
  <button type="submit" name="register">Sign Up</button>
</form>

<a href="Signinpage.php" class="signin-link">Already have an account? Sign In</a>
</div>
</div>

 <!-- FOOTER -->
  <div class="footer__container">
    <a href="index.html" id="footer__logo"><i class="fas fa-gem"></i>INTELLI RESOLVERS</a>
    <div class="footer__links">
      <div class="footer__link--items">
        <h3>Support</h3>
        <a href="privacypolicy.html">Privacy Policy</a>
        <a href="termsofservice.html">Terms of Service</a>
      </div>
      <div class="footer__link--items">
        <h3>About Us</h3>
        <a href="contactus.html">Contact Us </a>
        <a href="websitecredits.html">Website Credits</a>
      </div>
    </div>
    <div class="social__icons">
      <a href="#"><i class="fab fa-facebook"></i></a>
      <a href="#"><i class="fab fa-instagram"></i></a>
      <a href="#"><i class="fab fa-twitter"></i></a>
      <a href="#"><i class="fab fa-youtube"></i></a>
      <a href="#"><i class="fab fa-linkedin"></i></a>
    </div>
    <p class="website__rights">© INTELLI RESOLVERS 2025. All rights reserved.</p>
  </div>
<!-- JS MOBILE MENU -->
  <script>
    const mobileMenu = document.getElementById('mobile-menu');
    const navbarMenu = document.querySelector('.navbar__menu');
    mobileMenu.addEventListener('click', () => {
      navbarMenu.classList.toggle('active');
      mobileMenu.classList.toggle('is-active');
    });
  </script>

<script>
const mobileMenu = document.getElementById('mobile-menu');
const navbarMenu = document.querySelector('.navbar__menu');
mobileMenu.addEventListener('click', () => {
  navbarMenu.classList.toggle('active');
  mobileMenu.classList.toggle('is-active');
});
</script>

</body>
</html>
