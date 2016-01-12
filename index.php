<?php
/**
* Twitter Search
*
* PHP version 5.0
*
* @author     Isi Roca
* @copyright  Copyright (C) 2016 Isi Roca
* @link       http://isiroca.com
* @since      File available since Release 1.0.0
* @license    https://opensource.org/licenses/MIT  The MIT License (MIT)
* @see        https://github.com/IsiRoca/Twitter-Search/issues
*
*/

// Require autoload file
require_once dirname(__FILE__) . '/src/autoload.php';

// Get Twitter connection
$twitter = new Twitter($consumerKey, $consumerSecret, $oauthAccessToken, $oauthAccessTokenSecret);

// Get query from form
if(isset($_GET['q'])){
    $query = $_GET['q'];

    if (!empty($query)) {
        $q = $twitter->search($query);
        $results = array_slice($q, 0, $maxResponse);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Twitter Search</title>

    <!-- Bootstrap core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

    <!-- Custom styles for this template -->
    <link href="assets/css/style.css" rel="stylesheet">


    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="container">
      <div class="header clearfix">
        <nav>
          <ul class="nav nav-pills pull-right">
            <li role="presentation" class="active"><a href="https://github.com/IsiRoca" target="_blank">Fork Me</a></li>
          </ul>
        </nav>
        <h3 class="text-muted">Twitter Search</h3>
      </div>

      <div class="jumbotron text-center">
        <h1>Twitter Search</h1>
        <form name="twitterQuery" class="form-inline" action="" method="GET">
            <div class="input-group input-group-lg col-lg-10">
                <input name="q" type="text" class="form-control input-lg" id="searchTwitter" placeholder="Example: #barcelona" value="<?php if ((isset($query)) && (!empty($query))) { echo $query; }?>"
                data-toggle="tooltip" data-placement="bottom" title="Type your search here...">
            </div>
            <div class="input-group input-group-lg col-lg-1">
                <button type="submit" class="btn btn-lg btn-success" data-loading-text="..."><span class="glyphicon glyphicon-search"></span></button>
            </div>
        </form>
      </div>
        <!-- Alert Box -->
        <div id="formAlert" class="alert alert-danger">
          <a href="#" class="close" data-dismiss="alert">&times;</a>
          <strong>Warning!</strong> Type your query in the input search and try again.
        </div>

      <div class="row marketing">
        <?php if (!empty($query)): ?>
        <div class="col-lg-12">
          <h4>Response for your Query "<?php echo $query;?>"</h4>
          <hr class="main-top">
            <!-- Get Twitter API Response -->
            <?php foreach ($results as $result): ?>
                <div class="media">
                  <div class="media-left media-top">
                    <a href="http://twitter.com/<?php echo $result->user->screen_name ?>" target="_blank">
                        <img class="media-object" src="<?php echo htmlspecialchars($result->user->profile_image_url) ?>" alt="<?php echo htmlspecialchars($result->user->name);?>">
                    </a>
                  </div>
                  <div class="media-body">
                    <h4 class="media-heading">
                    <?php echo Twitter::format($result, $query); ?>
                    </h4>
                        <small>
                            Published by
                            <a href="http://twitter.com/<?php echo $result->user->screen_name;?>" target="_blank">
                                <?php echo htmlspecialchars($result->user->name);?>
                            </a>
                            at <?php echo date("j.n.Y H:i", strtotime($result->created_at)) ?>
                        </small>
                  </div>
                </div>

                <hr class="main-response">
            <?php endforeach ?>
        </div>
        <?php endif ?>

      </div>

      <footer class="footer">
        <p>&copy; 2016 <a href"http://isiroca.com" target="_blank">Isi Roca.</a></p>
      </footer>

    </div> <!-- /container -->

    <script src="assets/js/script.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
  </body>
</html>