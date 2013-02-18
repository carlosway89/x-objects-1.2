<?php global $business_object; $f = $business_object; ?>
<div class="file-upload-wrapper">
    <div id="<?php echo $f->html_id; ?>" class="edit xo-file-uploader ii-<?php echo $f->html_id; ?>_image_file">
        <noscript>
            <p>Please enable JavaScript to use file uploader.</p>
            <!-- or put a simple form for upload here -->
        </noscript>
    </div>
    <input type="hidden" name="<?php echo $f->html_id; ?>_image_file" id="<?php echo $f->html_id; ?>_image_file" value=""/>
    <script>
    </script>
</div>
