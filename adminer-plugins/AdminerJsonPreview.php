<?php

/**
 * Displays JSON preview as a table.
 *
 * @link https://github.com/pematon/adminer-plugins
 *
 * @author Peter Knut
 * @copyright 2014-2018 Pematon, s.r.o. (http://www.pematon.com/)
 */
class AdminerJsonPreview extends \Adminer\Plugin
{
    const MAX_TEXT_LENGTH = 100;

    /** @var int */
    private $maxLevel;

    /** @var bool */
    private $inTable;

    /** @var bool */
    private $inEdit;

    /** @var int */
    private $maxTextLength;

    /**
     * @param int $maxLevel Max. level in recursion. 0 means no limit.
     * @param bool $inTable Whether apply JSON preview in selection table.
     * @param bool $inEdit Whether apply JSON preview in edit form.
     * @param int $maxTextLength Maximal length of string values. Longer texts will be truncated with ellipsis sign '…'.
     *                           0 means no limit.
     */
    public function __construct($maxLevel = 0, $inTable = true, $inEdit = true, $maxTextLength = self::MAX_TEXT_LENGTH)
    {
        $this->maxLevel = $maxLevel;
        $this->inTable = $inTable;
        $this->inEdit = $inEdit;
        $this->maxTextLength = $maxTextLength;
    }

    /**
     * Prints HTML code inside <head>.
     */
    public function head()
    {
        ?>

        <style>
            /* Table */
            .json {
                width: auto;
                border-collapse: collapse;
                border-spacing: 0;
                margin: 4px 0;
                border: 1px solid #999;
                font-size: 110%;
            }

            .json tr {
                border-bottom: 1px solid #999;
            }

            .json tr:last-child {
                border-bottom: none;
            }

            .checkable .json .checked th, .checkable .json .checked td {
                background: transparent;
            }

            .json th {
                padding: 0;
                width: 1px;
                border-right: 1px solid #999;
                border-bottom: none;
            }

            .json td {
                padding: 0;
                border: 0;
            }

            .json code {
                display: block;
                background: transparent;
                padding: 2px 3px;
                white-space: normal;
            }

            .json .json {
                width: 100%;
                border: none;
                margin: 0;
            }

            /* Togglers */
            a.json-icon {
                display: inline-block;
                padding: 0;
                overflow: hidden;
                background-image: url("<?php echo Adminer\ME; ?>file=down.gif");
                background-position: center center;
                background-repeat: no-repeat;
                text-indent: -50px;
                vertical-align: middle;
            }

            a.json-link {
                width: auto;
                padding-left: 18px;
                background-position: left center;
                text-indent: 0;
            }

            a.json-link span {
                color: #fff;
                padding: 0 5px;
            }

            a.json-icon.json-up {
                background-image: url("<?php echo Adminer\ME; ?>file=up.gif");
            }

            /* No javascript support */
            .nojs .json-icon, .nojs .json-link {
                display: none;
            }

            .nojs .json {
                display: table !important;
            }
        </style>

        <script <?php echo Adminer\nonce(); ?>>
            (function(document) {
                "use strict";

                document.addEventListener("DOMContentLoaded", init, false);

                function init() {
                    const links = document.querySelectorAll('a.json-icon');

                    for (let i = 0; i < links.length; i++) {
                        links[i].addEventListener("click", function(event) {
                            event.preventDefault();
                            toggleJson(this);
                        }, false);
                    }
                }

                function toggleJson(button) {
                    const index = button.dataset.index;

                    const obj = document.getElementById("json-code-" + index);
                    if (!obj) return;

                    if (obj.style.display === "none") {
                        button.className += " json-up";
                        obj.style.display = "";
                    } else {
                        button.className = button.className.replace(" json-up", "");
                        obj.style.display = "none";
                    }
                }
            })(document);
        </script>

        <?php
    }

    public function selectVal(&$val, $link, array $field, $original)
    {
        static $counter = 1;

        if (!$this->inTable) {
            return;
        }

        if ($this->isJson($field, $original) && ($json = json_decode($original, true)) !== null) {
            $val = "<a class='icon json-icon' href='#' title='JSON' data-index='$counter'>JSON</a> " . $val;
            if (is_array($json)) {
                $val .= $this->convertJson($json, 1, $counter++);
            }
        }
    }

    public function editInput($table, array $field, $attrs, $value)
    {
        static $counter = 1;

        if (!$this->inEdit) {
            return;
        }

        if ($this->isJson($field, $value) && ($json = json_decode($value, true)) !== null && is_array($json)) {
            echo "<a class='icon json-icon json-link' href='#' title='JSON' data-index='$counter'><span>JSON</span></a><br/>";
            echo $this->convertJson($json, 1, $counter++);
        }
    }

    private function isJson(array $field, $value)
    {
        return $field["type"] == "json" || (is_string($value) && in_array(substr($value, 0, 1), ['{', '[']));
    }

    private function convertJson(array $json, $level = 1, $id = 0)
    {
        $value = "";

        $value .= "<table class='json'";
        if ($level === 1 && $id > 0) {
            $value .= "style='display: none' id='json-code-$id'";
        }
        $value .= ">";

        foreach ($json as $key => $val) {
            $value .= "<tr><th><code>" . Adminer\h($key) . "</code>";
            $value .= "<td>";

            if (is_array($val) && ($this->maxLevel <= 0 || $level < $this->maxLevel)) {
                $value .= $this->convertJson($val, $level + 1);
            } elseif (is_array($val)) {
                // Shorten encoded JSON to max. length.
                $val = $this->truncate(json_encode($val));

                $value .= "<code class='jush-js'>" . Adminer\h(preg_replace('/([,:])([^\s])/', '$1 $2', $val)) . "</code>";
            } elseif (is_string($val)) {
                // Shorten string to max. length.
                $val = $this->truncate($val);

                // Add extra new line to make it visible in HTML output.
                if (preg_match("@\n$@", $val)) {
                    $val .= "\n";
                }

                $value .= "<code>" . nl2br(Adminer\h($val)) . "</code>";
            } elseif (is_bool($val)) {
                // Handle boolean values.
                $value .= "<code class='jush'>" . Adminer\h($val ? "true" : "false") . "</code>";
            } elseif (is_null($val)) {
                // Handle null value.
                $value .= "<code class='jush'>null</code>";
            } else {
                $value .= "<code class='jush'>" . Adminer\h($val) . "</code>";
            }
        }

        if (empty($json)) {
            $value .= "<tr><td>   </td></tr>";
        }

        $value .= "</table>";

        return $value;
    }

    private function truncate($value)
    {
        return $this->maxTextLength > 0 && mb_strlen($value, "UTF-8") > $this->maxTextLength
            ? mb_substr($value, 0, $this->maxTextLength - 1, "UTF-8") . "…"
            : $value;
    }
}