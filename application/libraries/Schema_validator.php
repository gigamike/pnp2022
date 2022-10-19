<?php

use Opis\JsonSchema\Validator;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\ValidationError;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\FormatContainer;

use OpisErrorPresenter\Contracts\PresentedValidationError;
use OpisErrorPresenter\Implementation\MessageFormatterFactory;
use OpisErrorPresenter\Implementation\PresentedValidationErrorFactory;
use OpisErrorPresenter\Implementation\ValidationErrorPresenter;

defined('BASEPATH') or exit('No direct script access allowed');

require(APPPATH . 'libraries/Json_schema_date_format.php');

class Schema_validator
{
    protected $CI;
    protected $custom_error = [];

    public function __construct()
    {
        $this->CI = & get_instance();
        $this->custom_error['minLength'] = "Minimum [MIN] character/s required.";
        $this->custom_error['maxLength'] = "Only [MAX] characters allowed.";
        $this->custom_error['pattern'] = "Invalid characters.";
        $this->custom_error['additionalProperties'] = "Additional field/s passed.";
    }

    public function validate($dataset, $schema_file)
    {
        $validator = new Validator();

        // Create a new FormatContainer
        $formats = new FormatContainer();

        // Register our date format
        $formats->add("string", "uhubdate", new Json_schema_date_format());

        // Set formats to be used by validator
        $validator->setFormats($formats);

        $loader = new Schema_loader();

        $loader->registerPath(FCPATH . 'json_schema', 'https://api.utilihub.io/json_schema');

        // Don't forget to add it to validator
        $validator->setLoader($loader);

        $schema = $loader->loadSchema("https://api.utilihub.io/json_schema/" . $schema_file);

        $result = $validator->schemaValidation(json_decode(json_encode($dataset)), $schema, -1);

        $return_data = [];
        if (!$result->isValid()) {
            $return_data['isValid'] = false;
            $return_data['errors'] = [];
            /** @var ValidationError $error */
            //log_message("debug", "all_error:".print_r($result->getErrors(), true));
            //print_r($result->getErrors());
            $i = 0;
            foreach ($result->getErrors() as $errors) {
                if (count($errors->subErrors())) {
                    foreach ($errors->subErrors() as $error) {
                        $return_data['errors'][$i]["field"] = implode("=>", $error->dataPointer());
                        $return_data['errors'][$i]["error_type"] = $error->keyword();
                        $return_data['errors'][$i]["error_message"] = $this->display_error($error);
                        break;
                    }
                } else {
                    $return_data['errors'][$i]["field"] = implode("=>", $errors->dataPointer());
                    $return_data['errors'][$i]["error_type"] = $errors->keyword();
                    $return_data['errors'][$i]["error_message"] = $this->display_error($errors);
                }
                $i++;
            }
        } else {
            $return_data['isValid'] = true;
        }
        return $return_data;
    }

  /**
   *  @param $errors https://opis.io/json-schema/1.x/php-validation-result.html#validationerror-object
   */
    protected function display_error($errors)
    {
        if (count($errors->subErrors())) {
            $message = "";
            foreach ($errors->subErrors() as $error) {
                //$message .= "field = " . implode("=>", $error->dataPointer()) . ",error_type = ". $error->keyword();
                //$return_data['errors'][$i]["error_message"] = $this->display_error($error);
                if (isset($this->custom_error[$error->keyword()])) {
                    if ($error->keyword() == "minLength") {
                        $message .= " OR " . str_replace("[MIN]", $error->keywordArgs()['min'], $this->custom_error[$error->keyword()]);
                    } elseif ($error->keyword() == "maxLength") {
                        $message .= " OR " . str_replace("[MAX]", $error->keywordArgs()['max'], $this->custom_error[$error->keyword()]);
                    } elseif ($error->keyword() == "additionalProperties") {
                        $message .= " OR " . $this->custom_error[$error->keyword()];
                    } else {
                        $message .= " OR " . $this->custom_error[$error->keyword()];
                    }
                } else {
                    $message .= " OR " . $this->get_error_message($errors);
                }
            }
            return trim($message, ' OR ');
        } else {
            if (isset($this->custom_error[$errors->keyword()])) {
                $message = "";
                if ($errors->keyword() == "minLength") {
                    $message = str_replace("[MIN]", $errors->keywordArgs()['min'], $this->custom_error[$errors->keyword()]);
                } elseif ($errors->keyword() == "maxLength") {
                    $message = str_replace("[MAX]", $errors->keywordArgs()['max'], $this->custom_error[$errors->keyword()]);
                } elseif ($errors->keyword() == "additionalProperties") {
                    $message = $this->custom_error[$errors->keyword()];
                } else {
                    $message = $this->custom_error[$errors->keyword()];
                }
                return $message;
            } else {
                return  $this->get_error_message($errors);
            }
        }
    }

    protected function get_error_message($error)
    {
        // Default strategy is AllErrors
        $presenter = new ValidationErrorPresenter(
            new PresentedValidationErrorFactory(
                new MessageFormatterFactory()
            )
        );

        $presented = $presenter->present($error);
        $messages = array_map(static function (PresentedValidationError $error) {
            return $error->toArray();
        }, $presented);

        $output = isset($messages[0]) ? $messages[0]: [];

        if(isset($output['message']))
            return $output['message'];
        else
            return null;
    }
}
