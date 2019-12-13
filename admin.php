<?php
require("config/config.php");

session_start();
$action = (isset($_GET['action']))?$_GET['action']:"";
$username = (isset($_SESSION['username']))?$_SESSION['username']:"";

if ( $action != "login" && $action != "logout" && !$username ){
    login();
    exit();
}

switch ($action){
    case 'login':
    login();
    break;
  case 'logout':
    logout();
    break;
  case 'newArticle':
    newArticle();
    break;
  case 'editArticle':
    editArticle();
    break;
    case 'deleteArticle':
    deleteArticle();
    break;
  default:
    listArticles();
}

function login(){
    $results = array();
    $results['pageTitle'] = "Admin Login | News Widget";

    if (isset($_POST['login'])){
        if ($_POST['username'] == ADMIN_USERNAME && $_POST['password'] == ADMIN_PASSWORD){
            $_SESSION['username'] = ADMIN_USERNAME;
            header("Location: admin.php");
        }else {
            $_SESSION['errorMessage'] =  "Incorrect username or password. Please try again.";
            require(TEMPLATE_PATH."admin/loginForm.php");
        }
    }
    else {
        require(TEMPLATE_PATH."admin/loginForm.php");
    }

}

function logout(){
    unset($_SESSION['username']);
    header("Location: admin.php");
}

function newArticle(){
    $results = array();
    $results['pageTitle'] = "New Article";
    $results['formAction'] = "newArticle";

    if(isset($_POST['saveChanges'])){
       $article = new Article;
       $article->storeFromValues($_POST);
       $article->insert();
       header("Location: admin.php/?status=changesSaved");
    }elseif (isset($_POST['cancel'])){
        header("Location: admin.php");
    }else{
        $article = new Article;
        require(TEMPLATE_PATH."admin/editArticle.php");
    }
}

function editArticle(){
    $results = array();
    $results['pageTitle'] = "Edit Article";
    $results['formAction'] = "editArticle";

    if(isset($_POST['saveChanges'])){
        if (!$article = Article::getById((int)$_POST['articleId'])){
            header("Location: admin.php/?error=articleNotFound");
            return;
        }
        $article->storeFromValues($_POST);
        $article->update();
        header("Location: admin.php/?status=changesSaved");

    }elseif (isset($_POST['cancel'])){
        header("Location: admin.php");
    }else{
        $results['article'] = Article::getById((int)$_POST['articleId']);
        require(TEMPLATE_PATH."admin/editArticle.php");
    }

    function deleteArticle(){
        if (!$article = Article::getById((int)$_POST['articleId'])){
            header("Location: admin.php/?error=articleNotFound");
            return;
        }
        $article->delete();
        header("Location: admin.php/?status=error=articleNotFound");
    }
    function listArticles(){
        $results = array();
        $data = Article::getList();
        $results['articles'] = $data['results'];
        $results['totalRows'] = $data['totalRows'];
        $results['pageTitle'] = "All Articles";

        if (isset($_GET['error'])){
            if ($_GET['error'] == "articleNotFound") $results['errorMessage'] = "Error: Article Not found";
        }

        if (isset($_GET['status'])){
            if ($_GET['status'] == "changesSaved") $results['statusMessage'] = "Changes has being saved";
            if ($_GET['status'] == "articleDeleted") $results['statusMessage'] = "Article deleted";
        }
        require(TEMPLATE_PATH."admin/listArticles.php");
    }
}