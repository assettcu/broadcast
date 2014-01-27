<div class="ui-state-default ui-corner-all calign" style="padding:10px;margin-bottom:15px;">
    ASSETT Broadcast is a service designed to let you broadcast <i>one message</i> across many media platforms.
</div>

<?php
$flashes = new Flashes;
$flashes->render();
?>

<div class="grand-brand-containers">
    <div class="brand-containers">
        <div class="brand">
            <?php
            $image = StdLib::load_image_source("facebook_logo","");
            $imager = new Imager(StdLib::make_path_local($image));
            $imager->add_attribute("style", "opacity:0.5;");
            $imager->resize("100");
            $imager->render();
            ?>
            Facebook
        </div>
        <div class="brand">
            <?php
            $image = StdLib::load_image_source("twitter_logo","");
            $imager->init(StdLib::make_path_local($image));
            $imager->resize("100");
            $imager->render();
            ?>
            Twitter
        </div>
        <div class="brand">
            <?php
            $image = StdLib::load_image_source("googleplus_logo","");
            $imager->init(StdLib::make_path_local($image));
            $imager->resize("100");
            $imager->render();
            ?>
            Google Plus
        </div>
        <div class="brand">
            <?php
            $image = StdLib::load_image_source("texting","");
            $imager->init(StdLib::make_path_local($image));
            $imager->resize("100");
            $imager->render();
            ?>
            Text Message
        </div>
        <div class="brand">
            <?php
            $image = StdLib::load_image_source("mail_logo","");
            $imager->init(StdLib::make_path_local($image));
            $imager->resize("100");
            $imager->render();
            ?>
            Email
        </div>
    </div>
</div>