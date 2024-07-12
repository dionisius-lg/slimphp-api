<?php

use \Psr\Container\ContainerInterface as Container;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Slim\Http\Stream;

class FilesController extends Controller {

    /**
     *  variable initialization
     *  @param {Container} $cont
     */
    public function __construct(Container $cont)  {
        parent::__construct($cont);
    }

    /**
     *  download file
     *  @param {Request} $req, {Response} $res, {array} $args
     *  @return {stream} file || {array} $handler
     */
    public function download(Request $req, Response $res, $args) {
        $encrypted = $args['encrypted'];

        try {
            $decrypted = json_decode(decrypt($encrypted), true);

            if (empty($decrypted)) {
                throw new Exception("Not found");
            }

            $file_data = [
                'filename' => $decrypted['filename'],
                'path' => $decrypted['path'],
                'size' => $decrypted['size'],
                'mime' => $decrypted['mime'],
            ];

            if (!file_exists("{$file_data['path']}/{$file_data['filename']}")) {
                throw new Exception("Not found");
                
            }

            $stream = fopen("{$file_data['path']}/{$file_data['filename']}", 'r+');

            return $res->withBody(new Stream($stream))
                       ->withStatus(200)
                       ->withHeader("Content-Disposition", "attachment; filename={$file_data['filename']}")
                       ->withHeader("Content-Type", $file_data['mime'])
                       ->withHeader("Content-Length", $file_data['size']);
        } catch (Exception $e) {
            $handler = $this->cont->get('notFoundDataHandler');
            return $handler($req, $res, 'File not found');
        }
    }

}