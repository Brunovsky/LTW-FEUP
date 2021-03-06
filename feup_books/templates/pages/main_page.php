<?php require_once __DIR__ . '/../common/sort_dropdown.php' ?>

<div id="main_page" class="page">

    <div id="main_page_posts"></div>
    
    <div id="main_page_aside">
        <div id="aside_create_post" class="aside_div">
            <a onclick="createPostButton()"><button type="button">CREATE POST</button></a>
            <a onclick="createChannelButton()"><button type="button">CREATE CHANNEL</button></a>
        </div>

        <div id="aside_channels" class="aside_div">
            <header>CHANNELS</header>
            <ul></ul>
        </div>

        <div id="aside_favorite_post" class="aside_div">
            <header>FAVORITE POSTS</header>
            <ul><ul>
        </div>

        <div id="aside_footer" class="aside_div">
            <?php require_once __DIR__ . '/../common/footer.php' ?>
        </div>

    </div>

    <script src="javascript/pages/main_page.js" defer></script>
    <script src="javascript/common/post_buttons.js" defer></script>
</div>