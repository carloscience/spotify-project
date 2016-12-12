
<?
// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

$CLIENT_ID = 'e4a75b7c1bed4e72bad4d1b36885be74';
$CLIENT_SECRET = 'dcbc136155b94b6aa568c0515a6a16e6';
$REDIRECT_URI = 'http://localhost:8888/callback';

$app = new Silex\Application();
$app['debug'] = true;

//define your silex routes as follows:
$app->get('/', function() {
?>
<!doctype html>
<html>
<head>
  <title>Acme Artist Search Engine</title>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.0/jquery.min.js"></script>
  <script src="https://unpkg.com/react@15/dist/react.js"></script>
  <script src="https://unpkg.com/react-dom@15/dist/react-dom.js"></script>
  <!--<script src="https://unpkg.com/babel-standalone@6.15.0/babel.min.js"></script>-->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/6.19.0/babel.min.js"></script>
  <!--<script src="js/components.js" type="text/jsx"></script>-->
</head>
<body>
  <div id="search-artists"></div>
  <div id="artists-list"></div>
  <script type="text/jsx">
  
  class SearchArtists extends React.Component {

    constructor() {
      super();
      this.state = {
        artists: []
      }
    }

    render() {

        if(this.state.artists.length > 0){
          var view = (<div>
            <ul>
              {this.state.artists.map( artist => <li key={artist.id}><a id="{artist.id}" href="#">{artist.name}</a></li>)}
            </ul>
          </div>)
        }

        return(<div>
                <label>Artist:</label>
                <input className="search" type="text"></input>
                <button onClick={this._handleClick.bind(this)} type="submit">Search</button>
                {view}
              </div>);
    }

    _handleClick(event) {
      event.preventDefault();
      $('.artists').empty();
      var query = $('.search').val();
      var _this = this;
      $.getJSON('/search?name=' + query, function(data) {
        var artistList = data.artists;
        _this.setState({"artists":artistList});
        return (
          <div>
            <ul>
              {artistList.map( artist => <li key={artist.id}><a id={artist.id} href="#">{artist.name}</a></li>)}
            </ul>
          </div>
        );
      });
    }
  }

  ReactDOM.render(<SearchArtists />, document.getElementById('search-artists'));

  </script>
</body>
</html>
<?
  return '';
});

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->get('/artist', function(Request $request, Silex\Application $app) {
  $api = new SpotifyWebAPI\SpotifyWebAPI();
  $id = "";
  if (isset($_GET['id'])) {
    $id = $_GET['id'];
  }
  $artist = $api->getArtist($id);

  return $app->json(array('artist' => $artist), 200);
});

$app->get('/search', function(Request $request, Silex\Application $app) {
  $api = new SpotifyWebAPI\SpotifyWebAPI();
  $search_query = "";
  if (isset($_GET['name'])) {
    $search_query = $_GET['name'];
  }

  $results = $api->search($search_query, 'artist');
  
  $artist_list = [];

  // Enter username and password
  $username = 'root';
  $password = 'root';

  // Create database connection using PHP Data Object (PDO)
  $db = new PDO("mysql:host=localhost;dbname=acme", $username, $password);

  // Identify name of table within database
  $table = 'artists';

  foreach ($results->artists->items as $artist) {
      #echo $app->json($artist, 200);

      $artist_image = $artist->images[1]->url;
      $artist_genres = json_encode($artist->genres);
      
      $artist_info = array("id" => $artist->id,
                          "name" => $artist->name,
                          "genres" => $artist->genres,
                          "image" => $artist_image,
                          "popularity" => $artist->popularity);

      array_push($artist_list, $artist_info);

      /*
      $sql = "INSERT INTO `artists` (`name`, `genres`, `image`, `popularity`) VALUES ('$artist->name','$artist_genres','$artist_image','$artist->popularity');";


      $stmt = $db->query($sql);
      */
      // Close connection to database
      

      #echo $artist->name, '<br>';
  }
  
  return $app->json(array('artists' => $artist_list), 200);
});

// ... definitions

$app->run();
$db = NULL;
?>
