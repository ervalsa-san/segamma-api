<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world!');
        return $response;
    });

    $app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });

    // Get All Games
    $app->get('/games', function(Request $request, Response $response) {
        $db = $this->get(PDO::class);
        $query = $db->query('SELECT * FROM games');
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results));
        return $response->withHeader("Content-Type", "application/json");
    }); 

    // Get One Games
    $app->get('/games/{id}' , function(Request $request, Response $response, $args) {
        $db = $this->get(PDO::class);

        $query = $db->prepare('SELECT * FROM games WHERE id=?');
        $query->execute([$args['id']]);

        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($results[0]));
        
        return $response->withHeader("Content-Type", "application.json");
    });

    // Store Games Data
    $app->post('/games', function(Request $request, Response $response) {
        $parsedBody = $request->getParsedBody();

        $name = $parsedBody["name"];
        $slug = $parsedBody["slug"];
        $category = $parsedBody["category"];
        $url_icon = $parsedBody["url_icon"];
        $url_image = $parsedBody["url_image"];

        $db = $this->get(PDO::class);
        $query =$db->prepare('INSERT INTO games (name, slug, category, url_icon, url_image) VALUES (?, ?, ?, ?, ?)');
        $query->execute([$name, $slug, $category, $url_icon, $url_image]);

        $lastId = $db->lastInsertId();
        $response->getBody()->write(json_encode(
            ['message' => 'Game saved with id '. $lastId])
        );

        return $response->withHeader("Content-Type", "application/json");
    });

    // Update Games Data
    $app->put('/games/{id}', function(Request $request, Response $response, $args) {
        $parsedBody = $request->getParsedBody();
        $currentId = $args['id'];

        $name = $parsedBody["name"];
        $slug = $parsedBody["slug"];
        $category = $parsedBody["category"];
        $url_icon = $parsedBody["url_icon"];
        $url_image = $parsedBody["url_image"];

        $db = $this->get(PDO::class);
        $query = $db->prepare('UPDATE games SET name = ?, slug = ?, category = ?, url_icon = ?, url_image = ? WHERE id = ?');
        $query->execute([$name, $slug, $category, $url_icon, $url_image, $currentId]);

        $response->getBody()->write(json_encode(
            ['message' => "Game ($name) updated with id ". $currentId]
        ));

        return $response->withHeader("Content-Type", "application/json");
    });

    // Delete Games
    $app->delete('/games/{id}', function(Request $request, Response $response, $args) {
        $currentId = $args['id'];

        $db = $this->get(PDO::class);
        $query = $db->prepare('DELETE FROM games WHERE id = ?');
        $query->execute([$currentId]);

        $response->getBody()->write(json_encode(['message' => "Games delete with id " . $currentId]));

        return $response->withHeader("Content-Type", "application/json");
    });
};
