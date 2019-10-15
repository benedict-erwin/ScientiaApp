<!doctype html>
<html lang="en">

<head>
    <title>Smart Wizard - a JavaScript jQuery Step Wizard plugin</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Include Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <!-- Include SmartWizard CSS -->
    <link href="vendor/SmartWizard/dist/css/smart_wizard.css" rel="stylesheet" type="text/css" />

    <!-- Optional SmartWizard theme -->
    <link href="vendor/SmartWizard/dist/css/smart_wizard_theme_circles.css" rel="stylesheet" type="text/css" />
    <link href="vendor/SmartWizard/dist/css/smart_wizard_theme_arrows.css" rel="stylesheet" type="text/css" />
    <link href="vendor/SmartWizard/dist/css/smart_wizard_theme_dots.css" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" href="../assets/vendor/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="css/view.css" />
    <style>
        .container {
            margin-top: 5px;
        }

        .well {
            width: 100%;
            height: 275px;
            overflow-y: scroll;
            overflow-x: hidden;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>ScientiaApp Installation Wizard</h2>

        <!-- SmartWizard html -->
        <div id="smartwizard">
            <ul>
                <li>
                    <a href="#step-1">Intro<br />
                        <small>Welcome</small>
                    </a>
                </li>
                <li>
                    <a href="#step-2">Step 1<br />
                        <small>Readiness Check</small>
                    </a>
                </li>
                <li>
                    <a href="#step-3">Step 2<br />
                        <small>Database Configuration</small>
                    </a>
                </li>
                <li>
                    <a href="#step-4">Step 3<br />
                        <small>Web Apps Configuraton</small>
                    </a>
                </li>
                <li>
                    <a href="#step-5">Step 4<br />
                        <small>Installation</small>
                    </a>
                </li>
            </ul>

            <div>
                <div id="step-1" class="">
                    <p style="margin-top:25px;">
                        Welcome to ScientiaApp Installation Wizard
                        <br>This wizard will help You to install ScientiaApp step by step.
                        <br>Please following all of the instruction as they have been given to you.<br>
                        <ul>
                            <li>Click <strong>Next</strong> to begin the installation process</li>
                        </ul>
                        <button id="start-1" style="margin-left: 10px;" class="btn btn-success">NEXT</button>
                        <p>
                </div>
                <div id="step-2" class="">
                    <p style="margin-top:25px;">
                        Step 1: Readiness Check
                        <ul class="fa-ul">
                            <li id="txCek"><i class="fa-li fa fa-spin fa-refresh"></i><span>We're making sure Your server environment is ready for ScientiaApp being installed</span></li>
                            <li id="txPHP"><i class="fa-li fa fa-spin fa-refresh"></i><span>Checking PHP version<span></li>
                            <li id="txComposer"><i class="fa-li fa fa-spin fa-refresh"></i><span>Preparing composer installatiion</span></li>
                        </ul>
                        <pre id="output" class="well"></pre>
                        <span id="next2"></span>
                        <p>
                </div>
                <div id="step-3" class="">
                    <p style="margin-top:25px;">
                        <form id="form_container" class="step_dbconf">
                            <span id="form_73200" class="appnitro">
                                <div class="form_description">
                                    <h2 style="padding: 15px 0 0 15px;">Step 2: Database Configuration</h2>
                                    <p style="padding-left: 15px;">You are about to create a new database and user with this configuration.</p>

                                    <ul>
                                        <li id="li_1">
                                            <label class="description" for="element_1">DB Hostname </label>
                                            <div>
                                                <input id="tx_dbhost" name="element_1" class="element text medium" type="text" maxlength="255" value="" />
                                            </div>
                                            <p class="guidelines" required id="guide_1"><small>Your database hostname, (ex: localhost)</small></p>
                                        </li>
                                        <li id="li_2">
                                            <label class="description" for="element_2">Root User </label>
                                            <div>
                                                <input id="tx_dbuser" required name="element_2" class="element text medium" type="text" maxlength="255" value="" />
                                            </div>
                                            <p class="guidelines" id="guide_2"><small>Your MySQL root username</small></p>
                                        </li>
                                        <li id="li_3">
                                            <label class="description" for="element_3">Root Password </label>
                                            <div>
                                                <input id="tx_dbpass" required name="element_3" class="element text medium" type="password" maxlength="255" value="" />
                                            </div>
                                            <p class="guidelines" id="guide_3"><small>Your MySQL root password</small></p>
                                        </li>
                                        <button style="margin-left: 10px;" class="btn btn-success btnTest">Test Connection</button>
                                        <h3></h3>
                                        <p></p>
                                    </ul>
                                </div>
                                <ul>
                                    <li id="li_5">
                                        <label class="description" for="element_5">New DB Name </label>
                                        <div>
                                            <input id="new_db" required name="new_db" class="element text medium" type="text" maxlength="255" value="" />
                                        </div>
                                        <p class="guidelines" id="guide_5"><small>Your database name that about to create, ex: scientia_db</small></p>
                                    </li>
                                    <li id="li_6">
                                        <label class="description" for="element_6">New DB User </label>
                                        <div>
                                            <input id="new_user" required name="new_user" class="element text medium" type="text" maxlength="255" value="" />
                                        </div>
                                        <p class="guidelines" id="guide_6"><small>Your new database username</small></p>
                                    </li>
                                    <li id="li_7">
                                        <label class="description" for="element_7">New DB Password </label>
                                        <div>
                                            <input id="new_pass" required name="new_pass" class="element text medium" type="password" maxlength="255" value="" />
                                        </div>
                                        <p class="guidelines" id="guide_7"><small>Your new database password</small></p>
                                    </li>
                                </ul>
                                <button id="start-3" style="margin-left: 10px;" class="btn btn-success">NEXT</button>
                            </span>
                            <div id="footer">
                                Generated by <a href="http://www.phpform.org">pForm</a>
                            </div>
                        </form>
                        <ul id="next3"></ul>
                        <p>
                </div>
                <div id="step-4" class="">
                    <p style="margin-top:25px;">
                        <div id="form_container2">
                            <span id="form_73200" class="appnitro">
                                <div class="form_description">
                                    <h2 style="padding: 15px 0 0 15px;">Step 3: Web Apps Configuraton</h2>
                                    <p style="padding-left: 15px;">You are about to create a new database and user with this configuration.
                                    </p>
                                </div>
                                <ul>
                                    <li id="li_1">
                                        <label class="description" for="element_1">Base URL </label>
                                        <div>
                                            <input id="tx_baseurl" name="tx_baseurl" class="element text large" type="text" maxlength="255" value="" />
                                        </div>
                                        <p class="guidelines" id="guide_1"><small>Your web apps base url, end with "/", ( ex: http://domain.com/ )</small></p>
                                    </li>
                                    <li id="li_2">
                                        <label class="description" for="element_2">CMS User Fullname </label>
                                        <div>
                                            <input id="tx_fullname" name="tx_fullname" class="element text large" type="text" maxlength="255" value="" />
                                        </div>
                                        <p class="guidelines" id="guide_2"><small>Super user fullname</small></p>
                                    </li>
                                    <li id="li_3">
                                        <label class="description" for="element_3">CMS User Email</label>
                                        <div>
                                            <input id="tx_email" name="tx_email" class="element text large" type="email" maxlength="255" value="" />
                                        </div>
                                        <p class="guidelines" id="guide_3"><small>Super user email</small></p>
                                    </li>
                                    <li id="li_5">
                                        <label class="description" for="element_5">CMS User Phone</label>
                                        <div>
                                            <input id="tx_phone" name="tx_phone" class="element text medium" type="number" maxlength="255" value="" />
                                        </div>
                                        <p class="guidelines" id="guide_5"><small>Super user phone number</small></p>
                                    </li>
                                    <li id="li_6">
                                        <label class="description" for="element_6">CMS Username</label>
                                        <div>
                                            <input id="tx_user" name="tx_new_user" class="element text medium" type="text" maxlength="255" value="" />
                                        </div>
                                        <p class="guidelines" id="guide_6"><small>Super user username</small></p>
                                    </li>
                                    <li id="li_7">
                                        <label class="description" for="element_7">CMS User Password</label>
                                        <div>
                                            <input id="tx_pass" name="tx_new_pass" class="element text medium" type="password" maxlength="255" value="" />
                                        </div>
                                        <p class="guidelines" id="guide_7"><small>Super user password</small></p>
                                    </li>
                                    <button id="start-4" class="btn btn-success">NEXT</button>
                                </ul>
                            </span>
                            <div id="footer">
                                Generated by <a href="http://www.phpform.org">pForm</a>
                            </div>
                        </div>
                        <ul id="next3"></ul>
                        <p>
                </div>
                <div id="step-5" class="">
                    <p style="margin-top:25px;">
                        Final Destination: Installation
                        <ul class="fa-ul">
                            <li id="txInfo"><i class="fa-li fa fa-spin fa-refresh"></i>
                                <span>Please be patient...</span>
                            </li>
                        </ul>
                        <pre id="outputLast" class="well"></pre>
                        <span id="nextLast"></span>
                        <p>
                </div>
            </div>
        </div>

    </div>

    <!-- Include jQuery -->
    <script src="js/jquery.min.js"></script>
    <script src="js/view.js"></script>

    <!-- Include SmartWizard JavaScript source -->
    <script type="text/javascript" src="vendor/SmartWizard/dist/js/jquery.smartWizard.min.js"></script>

    <script type="text/javascript">
        var isOk = false;
        $(document).ready(function() {

            $('.step_dbconf').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
            });

            // Toolbar extra buttons
            var btnFinish = $('<button></button>')
                .text('Install')
                .addClass('btn btn-info btnFinish')
                .on('click', function() {
                    alert('Finish Clicked');
                });

            btnFinish.prop('disabled', true);
            // Smart Wizard
            $('#smartwizard').smartWizard({
                selected: 0,
                theme: 'arrows',
                transitionEffect: 'fade',
                keyNavigation: false,
                toolbarSettings: {
                    toolbarPosition: 'none',
                    toolbarExtraButtons: [btnFinish]
                },
                contentCache: false,
                noForwardJumping: true,
                showStepURLhash: false
            });

            // Initialize the leaveStep event
            $("#smartwizard").on("leaveStep", function(e, anchorObject, stepNumber, stepDirection) {
                if (stepDirection === 'forward' && stepNumber === 0) {
                    /* Dependencies Check */
                    $outPut = $('#output');
                    $.ajax({
                        type: 'post',
                        url: "ajaxcall.php",
                        data: {
                            step_name: 'svrcheck',
                            data: 'Dependencies Check'
                        },
                        success: function(data) {
                            let cmp = data.composer;
                            let php = data.php_70;

                            //php version
                            setTimeout(() => {
                                $("#txPHP > i").removeClass('fa-spin fa-refresh');
                                if (php.check === true) {
                                    isOk = true;
                                    $("#txPHP > i").addClass('fa-check');
                                    $("#txPHP > i").css('color', 'green');
                                    $("#txPHP > span").text('Your PHP Version is correct (' + php.version + ')');

                                    //composer
                                    setTimeout(() => {
                                        let txNext = (isOk) ? '<ul><li>Click <strong>Next</strong> to continue.</li></ul><button id="start-2" style="margin-left: 10px;" class="btn btn-success">NEXT</button>' : '';
                                        if (isOk) {
                                            $.ajax({
                                                type: 'post',
                                                url: "ajaxcall.php",
                                                data: {
                                                    step_name: 'composer_down',
                                                },
                                                success: function(data) {
                                                    $("#output").append(data);
                                                    $outPut.scrollTop(100);
                                                    $("#txComposer > span").text('Composer installing dependencies...');
                                                    $("#output").append("\nplease wait...\n");
                                                    $("#output").append("\n===================================================================\n");
                                                    $("#output").append("Executing Started");
                                                    $("#output").append("\n===================================================================\n");
                                                    $outPut.scrollTop(200);
                                                    $.ajax({
                                                        type: 'post',
                                                        url: "ajaxcall.php",
                                                        data: {
                                                            step_name: 'composer',
                                                        },
                                                        success: function(data) {
                                                            $("#output").append(data);
                                                            $outPut.scrollTop(400);
                                                            $("#output").append("\n===================================================================\n");
                                                            $("#output").append("Execution Ended");
                                                            $("#output").append("\n===================================================================\n");
                                                            $outPut.scrollTop(600);
                                                            $("#txComposer > span").text('Done, all dependencies are satisfied');
                                                            $("#txComposer > i").removeClass('fa-spin fa-refresh');
                                                            $("#txComposer > i").addClass('fa-check');
                                                            $("#txComposer > i").css('color', 'green');
                                                            $("#txCek > i").removeClass('fa-spin fa-refresh');
                                                            $("#txCek > i").addClass('fa-check');
                                                            $("#txCek > i").css('color', 'green');
                                                            $("#txCek > span").text("Done! We're ready for the next step.");
                                                            $("#next2").html(txNext);
                                                        }
                                                    });
                                                }
                                            });
                                        } else {
                                            $("#txComposer > span").text('Aborted!');
                                            $("#txComposer > i").removeClass('fa-spin fa-refresh');
                                            $("#txComposer > i").addClass('fa-close');
                                            $("#txComposer > i").css('color', 'green');
                                            $("#txCek > i").removeClass('fa-spin fa-refresh');
                                            $("#txCek > i").addClass('fa-close');
                                            $("#txCek > i").css('color', 'red');
                                            $("#txCek > span").text('Failed.');
                                        }

                                    }, 250);
                                } else {
                                    isOk = false;
                                    $("#txPHP > i").addClass('fa-close');
                                    $("#txPHP > i").css('color', 'red');
                                    $("#txPHP > span").text('Your PHP Version (' + php.version + ') is not compatible, required version is 7.0.0');
                                    $("#txCek > i").removeClass('fa-spin fa-refresh');
                                    $("#txCek > i").addClass('fa-close');
                                    $("#txCek > i").css('color', 'red');
                                    $("#txCek > span").text('Failed.');
                                    $("#txComposer > span").text('Aborted!');
                                    $("#txComposer > i").removeClass('fa-spin fa-refresh');
                                    $("#txComposer > i").addClass('fa-close');
                                    $("#txComposer > i").css('color', 'red');
                                }
                            }, 450);
                        }
                    });
                } else if (stepDirection === 'forward' && stepNumber === 3) {
                    setTimeout(() => {
                        $("#txInfo > span").text('Preparing Log and Cache...');
                        $("#outputLast").append('Create App/Cache and App/Log directory...\n');
                        $.ajax({
                            type: 'post',
                            url: "ajaxcall.php",
                            data: {
                                step_name: 'preparedir'
                            },
                            success: function(data) {
                                let txCreated = '';
                                let txPermission = '';
                                txCreated = 'Cache and Log directory:\n';
                                $(data.created).each(function(index, val) {
                                    txCreated += '\t' + val + '\n';
                                });
                                txPermission = '\nSet Permission for Controller & Model:\n';
                                $(data.permission).each(function(index, val) {
                                    txPermission += '\t' + val + '\n';
                                });
                                $("#outputLast").append(txCreated);
                                $("#outputLast").append(txPermission);

                                setTimeout(() => {
                                    $("#txInfo > span").text('Preparing Database: user and schema...');
                                    $("#outputLast").append('\n\nCreate new database\n');
                                    $.ajax({
                                        type: 'post',
                                        url: "ajaxcall.php",
                                        data: {
                                            step_name: 'createnewuserdb'
                                        },
                                        success: function(data) {
                                            $("#outputLast").append('Create new user\n');
                                            $("#outputLast").append('Grant default privileges for new user\n');

                                            setTimeout(() => {
                                                $("#txInfo > span").text('Preparing Database: generating default data...');
                                                $("#outputLast").append('\n\nImporting data...\n');
                                                $.ajax({
                                                    type: 'post',
                                                    url: "ajaxcall.php",
                                                    data: {
                                                        step_name: 'importdatabase'
                                                    },
                                                    success: function(data) {
                                                        $("#outputLast").append('All data imported successfully.\n');
                                                        setTimeout(() => {
                                                            $("#txInfo > span").text('Setting up ScientiaApp super user...');
                                                            $("#outputLast").append('\n\nGenerating super user...\n');
                                                            $.ajax({
                                                                type: 'post',
                                                                url: "ajaxcall.php",
                                                                data: {
                                                                    step_name: 'createsuperuser'
                                                                },
                                                                success: function(data) {
                                                                    $("#outputLast").append('User created with super role.\n');
                                                                    setTimeout(() => {
                                                                        $("#txInfo > span").text('Finalizing...');
                                                                        $("#outputLast").append('\n\nGenerating APP TOKEN...\n');
                                                                        $.ajax({
                                                                            type: 'post',
                                                                            url: "ajaxcall.php",
                                                                            data: {
                                                                                step_name: 'createenv'
                                                                            },
                                                                            success: function(data) {
                                                                                let msg = '';
                                                                                $("#outputLast").append('APP TOKEN generated.\n');
                                                                                $("#outputLast").append('JS TOKEN generated.\n');
                                                                                msg += 'Your web apps skeleton is ready\n\n'
                                                                                msg += '\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n';
                                                                                msg += '\nBASE_URL\t: ' + data.data.base_url + '\n';
                                                                                msg += '\nUSERNAME\t: ' + data.data.username + '\n';
                                                                                msg += '\nPASSWORD\t: ' + data.data.password + '\n';
                                                                                msg += '\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-==-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n';
                                                                                msg += '\n\nYOU CAN USE THIS INSTALLER SCRIPT TO MIGRATE YOUR WEB APPS TO PRODUCTION SERVER\n';
                                                                                msg += 'BUT REMEMBER,  THAT THIS INSTALLER SHOULD BE REMOVE IN PRODUCTION MODE\n';
                                                                                $("#outputLast").append(msg);
                                                                                $("#txInfo > span").text('We done here :)');
                                                                                $("#txInfo > i").removeClass('fa-spin fa-refresh');
                                                                                $("#txInfo > i").addClass('fa-check');
                                                                                $("#txInfo > i").css('color', 'green');
                                                                                $("#nextLast").html('<ul><li>Click <strong>Finish</strong> to start Your journey ;)</li></ul><button id="lastFinish" style="margin-left: 10px;" class="btn btn-success">FINISH</button>');
                                                                            }
                                                                        });
                                                                    }, 500);
                                                                }
                                                            });
                                                        }, 500);
                                                    }
                                                });
                                            }, 500);
                                        }
                                    });
                                }, 500);
                            }
                        });

                    }, 1000);
                }
            });

            $(document).on('click', '.btnTest', function(e) {
                e.preventDefault();
                connectionTest(true);
            });

            function connectionTest(msg = false, callback) {
                $('.btnTest').text('loading...');
                $.ajax({
                    type: 'post',
                    url: "ajaxcall.php",
                    data: {
                        step_name: 'testdb',
                        dbhost: $('#tx_dbhost').val(),
                        dbuser: $('#tx_dbuser').val(),
                        dbpass: $('#tx_dbpass').val()

                    },
                    success: function(data) {
                        res = false;
                        $('.btnTest').text('Test Connection');
                        if (data.success == true) {
                            if (msg) {
                                alert('connected!');
                            }
                            res = true;
                        } else {
                            alert(data.error);
                            res = false;
                        }
                        /* Execute callback if exist */
                        typeof callback === 'function' && callback(res);
                    }
                });
            }

            /* Intro Next Button */
            $(document).on('click', '#start-1', function() {
                $("#smartwizard").smartWizard('next');
            });

            /* Step-1 Next Button */
            $(document).on('click', '#start-2', function() {
                $("#smartwizard").smartWizard('next');
            });

            /* Step-2 Next Button */
            $(document).on('click', '#start-3', function(e) {
                e.preventDefault();
                let cekInput = $(".step_dbconf input").filter(function() {
                    return $.trim($(this).val()).length == 0
                }).length;
                if (cekInput > 0) {
                    alert('Please fill all input fields!');
                    $(".step_dbconf input").filter(function() {
                        if ($.trim($(this).val()).length == 0) {
                            $(this).focus();
                        }
                    });
                    return false;
                } else {
                    $("#smartwizard").addClass('sw-loading');
                    connectionTest(false, function(res) {
                        console.log(res);
                        if (res == false) {
                            $("#smartwizard").removeClass('sw-loading');
                            return false;
                        } else {
                            $.ajax({
                                type: 'post',
                                url: "ajaxcall.php",
                                data: {
                                    step_name: 'savedbconf',
                                    dbhost: $('#tx_dbhost').val(),
                                    dbuser: $('#tx_dbuser').val(),
                                    dbpass: $('#tx_dbpass').val(),
                                    new_db: $('input[name=new_db]').val(),
                                    new_user: $('input[name=new_user]').val(),
                                    new_pass: $('input[name=new_pass]').val()
                                },
                                success: function(data) {
                                    // indicate the ajax has been done, release the next step
                                    $("#smartwizard").removeClass('sw-loading');
                                    $("#smartwizard").smartWizard('next');
                                }
                            });
                        }
                    });
                }
            });

            /* Step-4 Next Button */
            $(document).on('click', '#start-4', function(e) {
                e.preventDefault();
                let cekInput = $("#form_73200 input").filter(function() {
                    return $.trim($(this).val()).length == 0
                }).length;
                if (cekInput > 0) {
                    alert('Please fill all input fields!');
                    $("#form_73200 input").filter(function() {
                        if ($.trim($(this).val()).length == 0) {
                            $(this).focus();
                        }
                    });
                    return false;
                } else {
                    if (isUrlValid($('#tx_baseurl').val()) === false) {
                        alert('Please use valid url!');
                        $('#tx_baseurl').focus();
                        return false;
                    }
                    if (/\/$/.test($('#tx_baseurl').val()) === false) {
                        alert('Please use valid url!');
                        $('#tx_baseurl').focus();
                        return false;
                    }
                    if (validateEmail($('#tx_email').val()) === false) {
                        alert('Please use valid email address!');
                        $('#tx_email').focus();
                        return false;
                    }

                    $.ajax({
                        type: 'post',
                        url: "ajaxcall.php",
                        data: {
                            step_name: 'savewebconf',
                            tx_baseurl: $('#tx_baseurl').val(),
                            tx_fullname: $('#tx_fullname').val(),
                            tx_email: $('#tx_email').val(),
                            tx_phone: $('#tx_phone').val(),
                            tx_user: $('#tx_user').val(),
                            tx_pass: $('#tx_pass').val()
                        },
                        success: function(data) {
                            // indicate the ajax has been done, release the next step
                            $("#smartwizard").removeClass('sw-loading');
                            $("#smartwizard").smartWizard('next');
                        }
                    });

                }
            });

            $(document).on('click', '#lastFinish', function(e) {
                $.ajax({
                    type: 'post',
                    url: "ajaxcall.php",
                    data: {
                        step_name: 'wedonehere'
                    },
                    success: function(data) {
                        window.location.replace('../');
                    }
                });
            });

        });
    </script>
</body>

</html>
