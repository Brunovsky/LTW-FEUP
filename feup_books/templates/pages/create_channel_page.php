<div id="create_channel" class="page">

    <div class="new_channel_page">
        <h1>Create a Channel</h1>
        <div id="new_channel">
            <form>
                <input type="text" name="channel_name" placeholder="Channel Name"/>
                <input type="file" name="upload-file" accept="image/*"/>
                <input type="submit" name="channel_submission" value="Create"/>
            </form>
        </div>
    </div>

    <script src="javascript/pages/create_channel_page.js" defer></script>

    <div id="create_page_aside">
        <div id="rules" class="aside_div">
            <h1>Creating Channel Rules</h1>
            <ul>
                <li>You can not change the name of your channel after creating it</li>
                <li>You can not delete your channel after creating it</li>
            </ul>
        </div>

        <div id="aside_footer" class="aside_div">
            <?php require_once __DIR__ . '/../common/footer.php' ?>
        </div>

    </div>
</div>