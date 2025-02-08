<?php

declare(strict_types=1);

use CliffordVickrey\Book2024\App\Http\Response;
use Webmozart\Assert\Assert;

header('Content-Type: text/html; charset=UTF-8');

$response = $response ?? new Response();
Assert::isInstanceOf($response, Response::class);

$content = $response->getAttribute(Response::ATTR_CONTENT, '');
$js = $response->getAttribute(Response::ATTR_JS, false);

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <title>FEC Reporter 2.0</title>
    <style>
        #app {
            font-size: .9em;
        }

        #app td, #app tr {
            white-space: nowrap;
        }
    </style>
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
                    <div class="card-footer">
                        <small>Copyright &copy; 2025 Clifford Vickrey. All rights
                            reserved, all wrongs <em>avenged</em></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($js) { ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
            crossorigin="anonymous"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const clearButton = document.querySelector("#app-clear-button");
            const dropDowns = document.querySelectorAll("#app-search-form select");

            if (null !== clearButton) {
                clearButton.addEventListener("click", e => {
                    dropDowns.forEach(dropDown => {
                        let blankValue = "";

                        if (dropDown.id === "app-state-filter") {
                            blankValue = "USA";
                        }

                        dropDown.value = blankValue;
                    });

                    clearButton.closest("form").submit();

                    e.stopPropagation();
                    e.preventDefault();
                    return false;
                });
            }

            dropDowns.forEach(dropDown => dropDown.addEventListener("change", () => dropDown.closest("form").submit()));
        });
    </script>
<?php } ?>
</body>
</html>
