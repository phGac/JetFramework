<jet-extends path="_layout/index.php"></jet-extends>

<jet-container name="content">

    <h1>Actual Currency</h1>

    <table>
        <tr>
            <th>Nombre</th>
            <th>Fecha</th>
            <th>Valor</th>
            <th>Unidad</th>
        </tr>
        <?php foreach ($actual as $currency) { ?>
        <tr>
            <th><?=$currency->nombre?></th>
            <th><?=date_format(date_create($currency->fecha), 'Y/m/d H:i')?></th>
            <th><?=$currency->valor?></th>
            <th><?=$currency->unidad_medida?></th>
        </tr>
        <?php } ?>
    </table>

</jet-container>