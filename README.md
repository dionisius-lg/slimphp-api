# SlimPHP API

[![Build Status](https://travis-ci.org/slimphp/Slim.svg?branch=3.x)](https://github.com/slimphp/Slim/tree/3.x)
[![License](https://poser.pugx.org/slim/slim/license)](https://packagist.org/packages/slim/slim)

This application is built with [SlimPHP](https://www.slimframework.com) 3.x

A skeleton for creating applications with [SlimPHP](https://www.slimframework.com) 3.x.

The framework source code can be found here: [slimphp/Slim](https://github.com/slimphp/Slim).

## Installation

1. Git clone or pull this source.
2. Download [Composer](http://getcomposer.org/doc/00-intro.md) or update `composer self-update`.
3. Run `composer update` inside application root directory.

You should now be able to visit the path to where you installed the app and see the default home page.

## Configuration

Read and edit `src/config.php`, then setup the 'Datasources' and any other
configuration relevant for your application.

## How to use

### Access Token

Request `POST` token with your credentials to get your access token, `http://localhost/slimphp-api/token`

The response you get in the form of a json object are:<br />
`request_time` is the time your request in unix<br />
`execution_time` is count request execution time (in seconds)<br />
`response_code` is response status code<br />
`success` is a status of the success of the request, `true` or `false`<br />
`total` is count of response data obtained<br />
`data` is the result data, contains: user id, token, token expire, refresh token, refresh token expire<br />

### Get List of All Data

Request `GET` list of all data (example: users) with header bearer type auth token, `http://localhost/slimphp-api/users`

The response you get in the form of a json object are:<br />
`request_time` is the time your request in unix<br />
`execution_time` is count request execution time (in seconds)<br />
`response_code` is response status code<br />
`success` is a status of the success of the request, `true` or `false`<br />
`total` is count of response data obtained<br />
`data` is the result data, contains array of object data. By default, number of data is limited to a max of 20 data<br />
`paging` is pagination info, contains: current, next, previous, first, last. It can be helpful to create pagination on Frontend App<br />

### Get Detail of Data

Request `GET` detail of data by id (example: users) with header bearer type auth token, `http://localhost/slimphp-api/users/2`

The response you get in the form of a json object are:<br />
`request_time` is the time your request in unix<br />
`execution_time` is count request execution time (in seconds)<br />
`response_code` is response status code<br />
`success` is a status of the success of the request, `true` or `false`<br />
`total` is count of response data obtained<br />
`data` is the result data, contains object row of data<br />

### Create New Data

Request `POST` to create new data (example: users) with header bearer type auth token, `http://localhost/slimphp-api/users`

The response you get in the form of a json object are:<br />
`request_time` is the time your request in unix<br />
`execution_time` is count request execution time (in seconds)<br />
`response_code` is response status code<br />
`success` is a status of the success of the request, `true` or `false`<br />
`total` is count of response data obtained<br />
`data` is the result data, contains last inserted data id<br />

### Update Existing Data

Request `PUT` to update existing data by id (example: users) with header bearer type auth token, `http://localhost/slimphp-api/users/id`

The response you get in the form of a json object are:<br />
`request_time` is the time your request in unix<br />
`execution_time` is count request execution time (in seconds)<br />
`response_code` is response status code<br />
`success` is a status of the success of the request, `true` or `false`<br />
`total` is count of response data obtained<br />
`data` is the result data, contains last updated data id<br />

### Delete Existing Data

Request `DELETE` to delete existing data by id (example: users) with header bearer type auth token, `http://localhost/slimphp-api/users/id`

The response you get in the form of a json object are:<br />
`request_time` is the time your request in unix<br />
`execution_time` is count request execution time (in seconds)<br />
`response_code` is response status code<br />
`success` is a status of the success of the request, `true` or `false`<br />
`total` is count of response data obtained<br />
`data` is the result data, contains last deleted data id<br />