<?php
defined('BASEPATH') OR exit('No direct script access allowed');


if (!function_exists('get_phone_placeholder')) {

    /**
     * Get_Phone_Placeholder
     *
     * get phone placeholder based on the environment
     *
     * @param	none
     * @return	string
     */
    function get_phone_placeholder($country_code) {
        $phone_placeholder = "";
        /**
         * CODE BRANCHING HERE - COUNTRY
         *      AU
         *      NZ
         *      US
         *      UK
         */
        switch ($country_code) {
            case "AU":
                $phone_placeholder = "E.g 0431000000";
                break;
            case "NZ":
                $phone_placeholder = "E.g 097000000";
                break;
            case "US":
                $phone_placeholder = "E.g 5555550000";
                break;
            case "UK":
                $phone_placeholder = "E.g 07712000000";
                break;
        }
        return $phone_placeholder;
    }

}

if (!function_exists('create_field')) {

    /**
     * Create_Field
     *
     * Create field html based on inputs
     *
     * @param	multiple
     * @return	none
     */
    function createField($field_type, $question, $placeholder, $value, $mandatory = null, $datamask = null, $div_class = null, $input_class_extra = '', $disabled = false, $read_only_fields = null) {
        if (is_null($div_class)) {
            $div_class = "col-md-8 col-sm-12 m-b-md";
        }
        if (is_null($mandatory)) {
            $mandatory = $question['mandatory'] === '1' ? true : false;
        }
        $readonly = '';
        if (!is_null($read_only_fields) && is_array($read_only_fields) && in_array($question['field_id'], $read_only_fields, true)) {
            $readonly = 'readonly';
        }
        ?>
        <div class="<?php echo $div_class; ?>">
            <label for="<?php echo $question['field_id'] ?>"><?php echo $question['label'] ?><span class="text-danger"> <?php echo $mandatory == true ? '*' : '' ?></span></label>
            <div class="form-group">
                <input type="<?php echo $field_type; ?>" name="<?php echo $question['field_id'] ?>" id="<?php echo $question['field_id'] ?>" class="form-control input-lg <?php echo $input_class_extra; ?>" <?php if (!is_null($placeholder)): ?>placeholder="<?php echo $placeholder; ?>" <?php endif; ?> value="<?php echo $value; ?>" <?php if (!is_null($datamask)): ?> data-mask="<?php echo $datamask; ?>"<?php endif; ?> <?php
                       echo $mandatory == true ? ' required' : ' ';
                       echo $disabled == true ? ' disabled' : '';
                       ?>  <?php echo $readonly; ?>>
            </div>
        </div>
        <?php
    }

}
