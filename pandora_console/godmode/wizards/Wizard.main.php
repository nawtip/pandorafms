<?php
/**
 * Extension to schedule tasks on Pandora FMS Console
 *
 * @category   Wizard
 * @package    Pandora FMS
 * @subpackage Wizard skel
 * @version    1.0.0
 * @license    See below
 *
 *    ______                 ___                    _______ _______ ________
 *   |   __ \.-----.--.--.--|  |.-----.----.-----. |    ___|   |   |     __|
 *  |    __/|  _  |     |  _  ||  _  |   _|  _  | |    ___|       |__     |
 * |___|   |___._|__|__|_____||_____|__| |___._| |___|   |__|_|__|_______|
 *
 * ============================================================================
 * Copyright (c) 2005-2019 Artica Soluciones Tecnologicas
 * Please see http://pandorafms.org for full contribution list
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation for version 2.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * ============================================================================
 */

// Begin.


 /**
  * Global Wizard generic class. Needs to be inherited.
  *
  * Used in Hostdevices class, Applications class and others, is the core of
  * Discovery proyect.
  */
class Wizard
{

    /**
     * Breadcrum
     *
     * @var array.
     */
    public $breadcrum;

    /**
     * Current page
     *
     * @var integer
     */
    public $page;

        /**
         * Target icon to be shown in discovery wizard list.
         *
         * @var string
         */
    public $icon;

    /**
     * Target label to be shown in discovery wizard list.
     *
     * @var string
     */
    public $label;

    /**
     * This wizard's url.
     *
     * @var string
     */
    public $url;

    /**
     * Result of wizard execution (0 - ok, 1 - not ok).
     *
     * @var integer
     */
    public $result;

    /**
     * Message to be delivered to user.
     *
     * @var string
     */
    public $msg;


    /**
     * Setter for breadcrum
     *
     * @param array $str Breadcrum.
     *
     * @return void
     */
    public function setBreadcrum(array $str)
    {
        $this->breadcrum = $str;
    }


    /**
     * Getter for breadcrum
     *
     * @return array Breadcrum.
     */
    public function getBreadcrum()
    {
        return $this->breadcrum;
    }


    /**
     * Add an element to breadcrum array.
     *
     * @param string $breads Elements to add to breadcrum.
     *
     * @return void
     */
    protected function addBreadcrum($breads)
    {
        if (empty($breads)) {
            return;
        }

        $this->breadcrum = array_merge($this->breadcrum, $breads);
    }


    /**
     * Setter for label
     *
     * @param string $str Label.
     *
     * @return void
     */
    public function setLabel(string $str)
    {
        $this->label = $str;
    }


    /**
     * Getter for label
     *
     * @return array Breadcrum.
     */
    public function getLabel()
    {
        return $this->label;
    }


    /**
     * Return units associated to target interval (in seconds).
     *
     * @param integer $interval Target interval.
     *
     * @return integer Unit.
     */
    public function getTimeUnit($interval)
    {
        $units = [
            1,
            60,
            3600,
            86400,
            604800,
            2592000,
            31104000,
        ];

        $size = count($units);
        for ($i = 0; $i < $size; $i++) {
            if ($interval < $units[$i]) {
                if (($i - 1) < 0) {
                    return 1;
                }

                return $units[($i - 1)];
            }
        }

        return $units[-1];
    }


    /**
     * Builder for breadcrum
     *
     * @param array   $urls Array of urls to be stored in breadcrum.
     * @param boolean $add  True if breadcrum should be added instead of
     *      overwrite it.
     *
     * @return void
     */
    public function prepareBreadcrum(array $urls, bool $add=false, bool $separator_beginning=false)
    {
        $bc = [];
        $i = 0;
        $count = 0;
        $array_size = count($urls);

        foreach ($urls as $url) {
            $count++;

            if ($url['selected'] == 1) {
                $class = 'selected';
            } else {
                $class = '';
            }

            $bc[$i] = '';

            if ($separator_beginning === true) {
                $bc[$i] .= '<span class="breadcrumb_link">&nbsp/&nbsp</span>';
            }

            $bc[$i]   .= '<span><a class="breadcrumb_link '.$class.'" href="'.$url['link'].'">';
            $bc[$i]   .= $url['label'];
            $bc[$i]   .= '</a>';
            if ($count < $array_size) {
                $bc[$i] .= '<span class="breadcrumb_link">&nbsp/&nbsp</span>';
            }

            $bc[$i] .= '</span>';

            $i++;
        }

        if ($add === true) {
            $this->addBreadcrum($bc);
        } else {
            $this->setBreadcrum($bc);
        }

    }


    /**
     * To be overwritten.
     *
     * @return void
     */
    public function run()
    {
        ui_require_css_file('wizard');
    }


    /**
     * Checks if environment is ready,
     * returns array
     *   icon: icon to be displayed
     *   label: label to be displayed
     *
     * @return array With data.
     **/
    public function load()
    {
        return [
            'icon'  => $this->icon,
            'label' => $this->label,
            'url'   => $this->url,
        ];
    }


    /**
     * Print breadcrum to follow flow.
     *
     * @return string Breadcrum HTML code.
     */
    public function printBreadcrum()
    {
        return implode('', $this->breadcrum);
    }


    /**
     * Prints a header for current wizard.
     *
     * @param boolean $return Return HTML or print it.
     *
     * @return string HTML code for header.
     */
    public function printHeader(bool $return=false)
    {
        $output = $this->printBreadcrum();
        if ($return === false) {
            echo $output;
        }

        return $output;
    }


    /**
     * Print input using functions html lib.
     *
     * @param array $data Input definition.
     *
     * @return string HTML code for desired input.
     */
    public function printInput($data)
    {
        if (is_array($data) === false) {
            return '';
        }

        switch ($data['type']) {
            case 'text':
            return html_print_input_text(
                $data['name'],
                $data['value'],
                ((isset($data['alt']) === true) ? $data['alt'] : ''),
                ((isset($data['size']) === true) ? $data['size'] : 50),
                ((isset($data['maxlength']) === true) ? $data['maxlength'] : 255),
                ((isset($data['return']) === true) ? $data['return'] : true),
                ((isset($data['disabled']) === true) ? $data['disabled'] : false),
                ((isset($data['required']) === true) ? $data['required'] : false),
                ((isset($data['function']) === true) ? $data['function'] : ''),
                ((isset($data['class']) === true) ? $data['class'] : ''),
                ((isset($data['onChange']) === true) ? $data['onChange'] : ''),
                ((isset($data['autocomplete']) === true) ? $data['autocomplete'] : '')
            );

            case 'image':
            return html_print_input_image(
                $data['name'],
                $data['src'],
                $data['value'],
                ((isset($data['style']) === true) ? $data['style'] : ''),
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['options']) === true) ? $data['options'] : false)
            );

            case 'text_extended':
            return html_print_input_text_extended(
                $data['name'],
                $data['value'],
                $data['id'],
                $data['alt'],
                $data['size'],
                $data['maxlength'],
                $data['disabled'],
                $data['script'],
                $data['attributes'],
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['password']) === true) ? $data['password'] : false),
                ((isset($data['function']) === true) ? $data['function'] : '')
            );

            case 'password':
            return html_print_input_password(
                $data['name'],
                $data['value'],
                ((isset($data['alt']) === true) ? $data['alt'] : ''),
                ((isset($data['size']) === true) ? $data['size'] : 50),
                ((isset($data['maxlength']) === true) ? $data['maxlength'] : 255),
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['disabled']) === true) ? $data['disabled'] : false),
                ((isset($data['required']) === true) ? $data['required'] : false),
                ((isset($data['class']) === true) ? $data['class'] : '')
            );

            case 'text':
            return html_print_input_text(
                $data['name'],
                $data['value'],
                ((isset($data['alt']) === true) ? $data['alt'] : ''),
                ((isset($data['size']) === true) ? $data['size'] : 50),
                ((isset($data['maxlength']) === true) ? $data['maxlength'] : 255),
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['disabled']) === true) ? $data['disabled'] : false),
                ((isset($data['required']) === true) ? $data['required'] : false),
                ((isset($data['function']) === true) ? $data['function'] : ''),
                ((isset($data['class']) === true) ? $data['class'] : ''),
                ((isset($data['onChange']) === true) ? $data['onChange'] : ''),
                ((isset($data['autocomplete']) === true) ? $data['autocomplete'] : '')
            );

            case 'image':
            return html_print_input_image(
                $data['name'],
                $data['src'],
                $data['value'],
                ((isset($data['style']) === true) ? $data['style'] : ''),
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['options']) === true) ? $data['options'] : false)
            );

            case 'hidden':
            return html_print_input_hidden(
                $data['name'],
                $data['value'],
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['class']) === true) ? $data['class'] : false)
            );

            case 'hidden_extended':
            return html_print_input_hidden_extended(
                $data['name'],
                $data['value'],
                $data['id'],
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['class']) === true) ? $data['class'] : false)
            );

            case 'color':
            return html_print_input_color(
                $data['name'],
                $data['value'],
                ((isset($data['class']) === true) ? $data['class'] : false),
                ((isset($data['return']) === true) ? $data['return'] : false)
            );

            case 'file':
            return html_print_input_file(
                $data['name'],
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['options']) === true) ? $data['options'] : false)
            );

            case 'select':
            return html_print_select(
                $data['fields'],
                $data['name'],
                ((isset($data['selected']) === true) ? $data['selected'] : ''),
                ((isset($data['script']) === true) ? $data['script'] : ''),
                ((isset($data['nothing']) === true) ? $data['nothing'] : ''),
                ((isset($data['nothing_value']) === true) ? $data['nothing_value'] : 0),
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['multiple']) === true) ? $data['multiple'] : false),
                ((isset($data['sort']) === true) ? $data['sort'] : true),
                ((isset($data['class']) === true) ? $data['class'] : ''),
                ((isset($data['disabled']) === true) ? $data['disabled'] : false),
                ((isset($data['style']) === true) ? $data['style'] : false),
                ((isset($data['option_style']) === true) ? $data['option_style'] : false),
                ((isset($data['size']) === true) ? $data['size'] : false),
                ((isset($data['modal']) === true) ? $data['modal'] : false),
                ((isset($data['message']) === true) ? $data['message'] : ''),
                ((isset($data['select_all']) === true) ? $data['select_all'] : false)
            );

            case 'select_from_sql':
            return html_print_select_from_sql(
                $data['sql'],
                $data['name'],
                ((isset($data['selected']) === true) ? $data['selected'] : ''),
                ((isset($data['script']) === true) ? $data['script'] : ''),
                ((isset($data['nothing']) === true) ? $data['nothing'] : ''),
                ((isset($data['nothing_value']) === true) ? $data['nothing_value'] : '0'),
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['multiple']) === true) ? $data['multiple'] : false),
                ((isset($data['sort']) === true) ? $data['sort'] : true),
                ((isset($data['disabled']) === true) ? $data['disabled'] : false),
                ((isset($data['style']) === true) ? $data['style'] : false),
                ((isset($data['size']) === true) ? $data['size'] : false),
                ((isset($data['trucate_size']) === true) ? $data['trucate_size'] : GENERIC_SIZE_TEXT)
            );

            case 'select_groups':
            return html_print_select_groups(
                ((isset($data['id_user']) === true) ? $data['id_user'] : false),
                ((isset($data['privilege']) === true) ? $data['privilege'] : 'AR'),
                ((isset($data['returnAllGroup']) === true) ? $data['returnAllGroup'] : true),
                $data['name'],
                ((isset($data['selected']) === true) ? $data['selected'] : ''),
                ((isset($data['script']) === true) ? $data['script'] : ''),
                ((isset($data['nothing']) === true) ? $data['nothing'] : ''),
                ((isset($data['nothing_value']) === true) ? $data['nothing_value'] : 0),
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['multiple']) === true) ? $data['multiple'] : false),
                ((isset($data['sort']) === true) ? $data['sort'] : true),
                ((isset($data['class']) === true) ? $data['class'] : ''),
                ((isset($data['disabled']) === true) ? $data['disabled'] : false),
                ((isset($data['style']) === true) ? $data['style'] : false),
                ((isset($data['option_style']) === true) ? $data['option_style'] : false),
                ((isset($data['id_group']) === true) ? $data['id_group'] : false),
                ((isset($data['keys_field']) === true) ? $data['keys_field'] : 'id_grupo'),
                ((isset($data['strict_user']) === true) ? $data['strict_user'] : false),
                ((isset($data['delete_groups']) === true) ? $data['delete_groups'] : false),
                ((isset($data['include_groups']) === true) ? $data['include_groups'] : false)
            );

            case 'submit':
            return '<div class="action-buttons" style="width: 100%">'.html_print_submit_button(
                ((isset($data['label']) === true) ? $data['label'] : 'OK'),
                ((isset($data['name']) === true) ? $data['name'] : ''),
                ((isset($data['disabled']) === true) ? $data['disabled'] : false),
                ((isset($data['attributes']) === true) ? $data['attributes'] : ''),
                ((isset($data['return']) === true) ? $data['return'] : false)
            ).'</div>';

            case 'checkbox':
            return html_print_checkbox(
                $data['name'],
                $data['value'],
                ((isset($data['checked']) === true) ? $data['checked'] : false),
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['disabled']) === true) ? $data['disabled'] : false),
                ((isset($data['script']) === true) ? $data['script'] : ''),
                ((isset($data['disabled_hidden']) === true) ? $data['disabled_hidden'] : false)
            );

            case 'switch':
            return html_print_switch($data);

            case 'interval':
            return html_print_extended_select_for_time(
                $data['name'],
                $data['value'],
                ((isset($data['script']) === true) ? $data['script'] : ''),
                ((isset($data['nothing']) === true) ? $data['nothing'] : ''),
                ((isset($data['nothing_value']) === true) ? $data['nothing_value'] : 0),
                ((isset($data['size']) === true) ? $data['size'] : false),
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['style']) === true) ? $data['selected'] : false),
                ((isset($data['unique']) === true) ? $data['unique'] : false)
            );

            case 'textarea':
            return html_print_textarea(
                $data['name'],
                $data['rows'],
                $data['columns'],
                ((isset($data['value']) === true) ? $data['value'] : ''),
                ((isset($data['attributes']) === true) ? $data['attributes'] : ''),
                ((isset($data['return']) === true) ? $data['return'] : false),
                ((isset($data['class']) === true) ? $data['class'] : '')
            );

            default:
                // Ignore.
            break;
        }

        return '';
    }


    /**
     * Prints a go back button redirecting to main page.
     *
     * @param string $url Optional target url.
     *
     * @return void
     */
    public function printGoBackButton($url=null)
    {
        if (isset($url) === false) {
            $url = ui_get_full_url(
                'index.php?sec=gservers&sec2=godmode/servers/discovery'
            );
        }

        $form = [
            'form'   => [
                'method' => 'POST',
                'action' => $url,
            ],
            'inputs' => [
                [
                    'arguments' => [
                        'name'       => 'submit',
                        'label'      => __('Go back'),
                        'type'       => 'submit',
                        'attributes' => 'class="sub cancel"',
                        'return'     => true,
                    ],
                ],
            ],
        ];

        $this->printForm($form);
    }


    /**
     * Print a block of inputs.
     *
     * @param array   $input  Definition of target block to be printed.
     * @param boolean $return Return as string or direct output.
     *
     * @return string HTML content.
     */
    public function printBlock(array $input, bool $return=false)
    {
        $output = '';
        if ($input['hidden'] == 1) {
            $class = ' hidden';
        } else {
            $class = '';
        }

        if (isset($input['class']) === true) {
            $class = $input['class'].$class;
        }

        if (is_array($input['block_content']) === true) {
            // Print independent block of inputs.
            $output .= '<li id="'.$input['block_id'].'" class="'.$class.'">';
            $output .= '<ul class="wizard">';
            foreach ($input['block_content'] as $input) {
                $output .= $this->printBlock($input, $return);
            }

            $output .= '</ul></li>';
        } else {
            if ($input['arguments']['type'] != 'hidden') {
                $output .= '<li id="'.$input['id'].'" class="'.$class.'">';
                $output .= '<label>'.$input['label'].'</label>';
                $output .= $this->printInput($input['arguments']);
                // Allow dynamic content.
                $output .= $input['extra'];
                $output .= '</li>';
            } else {
                $output .= $this->printInput($input['arguments']);
                // Allow dynamic content.
                $output .= $input['extra'];
            }
        }

        if ($return === false) {
            echo $output;
        }

        return $output;
    }


    /**
     * Print a form.
     *
     * @param array   $data   Definition of target form to be printed.
     * @param boolean $return Return as string or direct output.
     *
     * @return string HTML code.
     */
    public function printForm(array $data, bool $return=false)
    {
        $form = $data['form'];
        $inputs = $data['inputs'];
        $js = $data['js'];
        $cb_function = $data['cb_function'];
        $cb_args = $data['cb_args'];

        $output_head = '<form enctype="'.$form['enctype'].'" action="'.$form['action'].'" method="'.$form['method'];
        $output_head .= '" '.$form['extra'].'>';

        if ($return === false) {
            echo $output_head;
        }

        try {
            if (isset($cb_function) === true) {
                call_user_func_array(
                    $cb_function,
                    (isset($cb_args) === true) ? $cb_args : []
                );
            }
        } catch (Exception $e) {
            error_log('Error executing wizard callback: ', $e->getMessage());
        }

        $output = '<ul class="wizard">';

        foreach ($inputs as $input) {
            $output .= $this->printBlock($input, true);
        }

        $output .= '</ul>';
        $output .= '</form>';
        $output .= '<script>'.$js.'</script>';

        if ($return === false) {
            echo $output;
        }

        return $output_head.$output;

    }


    /**
     * Print a big button element (huge image, big text and link).
     *
     * @param array $data Element data (link, image...).
     *
     * @return void Only prints the element.
     */
    public static function printBigButtonElement($data)
    {
        if (isset($data['url']) === false) {
            $data['url'] = '#';
        }

        ?>
        <li class="discovery">
            <a href="<?php echo $data['url']; ?>">
                <div class="data_container">
                    <?php html_print_image($data['icon']); ?>
                    <br><label id="text_wizard">
                        <?php echo io_safe_output($data['label']); ?>
                    </label>
                </div>
            </a>
        </li>
        <?php
    }


    /**
     * Print a list of big buttons elements.
     *
     * @param array $list_data Array of data for printBigButtonElement.
     *
     * @return void Print the full list.
     */
    public static function printBigButtonsList($list_data)
    {
        echo '<ul>';
        array_map('self::printBigButtonElement', $list_data);
        echo '</ul>';
    }


}
