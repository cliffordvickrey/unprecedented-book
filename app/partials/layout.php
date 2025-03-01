<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\Http\Response;
use CliffordVickrey\Book2024\App\View\View;
use Webmozart\Assert\Assert;

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);
$view = $view ?? new View();
Assert::isInstanceOf($view, View::class);

$content = $response->getAttribute(Response::ATTR_CONTENT, '');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta content="IE=edge" http-equiv="X-UA-Compatible">
    <meta content="width=device-width,initial-scale=1" name="viewport">
    <meta content="https://www.cliffordvickrey.com/report2024/" property="og:url"/>
    <meta content="2024 FEC Reporter" property="og:title"/>
    <meta content="Shows profiles of contributors to 2024 presidential campaigns using data sourced from the FEC API and bulk fles."
          property="og:description"/>
    <meta content="https://www.cliffordvickrey.com/selfie.jpg" property="og:image"/>
    <meta content="Shows profiles of contributors to 2024 presidential campaigns using data sourced from the FEC API and bulk files."
          name="description"/>
    <title>FEC Reporter 2.0</title>
    <?= $view->emitCss('index'); ?>
</head>
<body>
<div id="app" class="container-fluid my-3">
    <div class="row">
        <div class="col-12">
            <div class="col-12 mt-3">
                <div class="card">
                    <h5 class="card-header">FEC Donor Profiler for the 2024 Election</h5>
                    <div class="card-body">
                        <!-- content -->
                        <?= $content; ?>
                        <!-- /content -->
                    </div>
                    <footer class="card-footer" id="app-footer">
                        <a href="https://github.com/cliffordvickrey/unprecedented-book">Source code</a>
                        |
                        Copyright &copy; 2025 Clifford Vickrey. All rights reserved, all wrongs <em>avenged</em>
                    </footer>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $view->emitEnqueuedScripts(); ?>
</body>
</html>
