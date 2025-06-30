<?php

/** Allow switching light and dark mode
* @link https://www.adminer.org/plugins/#use
* @author Jakub Vrana, https://www.vrana.cz/
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/
class AdminerDarkSwitcher extends \Adminer\Plugin
{
    public function head($dark = null)
    {
        ?>
<script <?php echo Adminer\nonce(); ?>%pcs-comment-end#* />
let adminerDark;
let adminerDarkModeTimeout = null;

function callAdminerDarkSet() {
    window.clearTimeout(adminerDarkModeTimeout);
    adminerDarkModeTimeout = window.setTimeout(adminerDarkSet, 200);
}

function adminerDarkSwitch() {
    // console.table('adminerDarkSwitch', adminerDark, !adminerDark);
    adminerDark = !adminerDark;
    callAdminerDarkSet();
}

function adminerDarkSet() {
    localStorage.setItem('adminer_dark_mode', adminerDark ? 1 : 0);
    console.log('adminerDarkSet', adminerDark);

    if (adminerDark) {
        document.body.classList.add('dark');
        document.body.classList.remove('light');
    } else {
        document.body.classList.remove('dark');
        document.body.classList.add('light');
    }
}

const saved = localStorage.getItem('adminer_dark_mode');
if (saved) {
    adminerDark = +saved;
    callAdminerDarkSet();
} else {
    adminerDark = +matchMedia('(prefers-color-scheme: dark)').matches;
}
</script>
<?php
    }

    public function navigation($missing)
    {
        echo "<big style='position: fixed; bottom: .5em; right: .5em; cursor: pointer;'>☀</big>"
            . Adminer\script("adminerDarkSwitch(); qsl('big').onclick = adminerDarkSwitch;") . "\n"
        ;
    }

    public function screenshot()
    {
        return "https://www.adminer.org/static/plugins/dark-switcher.gif";
    }

    protected $translations = array(
        'cs' => array('' => 'Dovoluje přepínání světlého a tmavého vzhledu'),
        'de' => array('' => 'Umschalten zwischen hellem und dunklem Design erlauben'),
        'ja' => array('' => 'ダークモードへの切替え'),
        'pl' => array('' => 'Zezwalaj na przełączanie trybu jasnego i ciemnego'),
    );
}
