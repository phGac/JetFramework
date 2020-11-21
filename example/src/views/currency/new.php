<jet-extends path="_layout/index.php"></jet-extends>

<jet-container name="content">

    <h1>New Currency</h1>

    <form method="post" action="/currency/new?XDEBUG_SESSION_START=PHPSTORM">
        <input type="hidden" name="csrf" value="<?=$csrf?>">
        <input type="text" value="">
        <input type="submit" value="Enviar">
    </form>

</jet-container>