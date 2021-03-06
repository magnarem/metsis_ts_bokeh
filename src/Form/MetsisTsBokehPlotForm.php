<?php
/*
 * @file
 * Contains \Drupal\metsis_ts_bokeh\Form\MetsisTsBokehPlotForm
 *
 * Form to show and manipulate the Plot
 *
 */
namespace Drupal\metsis_ts_bokeh\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\PrivateTempStoreFactory;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
/*
 * Class for the TS bokeh Plot form
 *
 * {@inheritdoc}
 *
 */
class MetsisTsBokehPlotForm extends FormBase {
/*
   *
   * Returns a unique string identifying the form.
   *  
   * The returned ID should be a unique string that can be a valid PHP function
   * name, since it's used in hook implementation names such as
   * hook_form_FORM_ID_alter().
   *
   * {@inheritdoc}
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'ts_bokeh_plot_form';
  }

/*
 * @param $form
 * @param $form_state
 *
 * @return mixed
 *
 * {@inheritdoc}
 */
 public function buildForm(array $form, FormStateInterface $form_state) {

  /*
   * build form based on JSON object returned by pybasket
   *
   * {@inheritdoc}
   */
  $tempstore = \Drupal::service('tempstore.private')->get('metsis_ts_bokeh');
  //$data_uri = $tempstore->get('metsis_ts_bokeh')->get('data_uri');
  //$items = $tempstore->get('items');
  \Drupal::logger('metsis_ts_bokeh')->debug('buildForm: yaxis form_state is: ' . $form_state->getValue('y_axis'));
  $isinit = $tempstore->get('isinit');

  //$element['hello'] = '<p>hello</p>';


 /*
  * Attach some js libraries to this form
  */
  $form['#attached']['library'][] = 'metsis_ts_bokeh/style';
  $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_js';
  $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_widgets';
  $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_tables';
  $form['#attached']['library'][] = 'metsis_ts_bokeh/bokeh_api';


/* We display the form above the plot with subit button on top */
/*$form['actions'] = [
  '#type' => 'actions',
];*/
$form['submit'] = [
  '#type' => 'button',
  '#value' => t('Plot'),
  '#ajax' => [
    'callback' => '::getPlotData',
  ],
];

$default_x_axis = "no default set";
$form['x_axis'] = [
  '#type' => 'select',
  '#options' => ['time' => 'time'],
  '#default_value' => $default_x_axis,
  '#description' => t(''),
  '#empty' => t(''),
];
$default_y_axis = "no default set";

$form['y_axis'] = [
  '#type' => 'select',
  '#options' => adc_get_ts_bokeh_plot_y_vars(),
  '#default_value' => $form_state->get('y_axis'),
  '#description' => t(''),
  '#empty' => t(''),
];

$form['items'] = [
  '#type' => 'value',
  '#value' => t('This is my stored value'),
];
  /*
   * Here we will display the plot
   */
  if($isinit) {
    $form['message']  = [
      '#type' => 'markup',
      '#markup' => '<div class="plot-container"><h3>Select variable to plot</h3></div>'
    ];
  }
  else {

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="plot-container"></div>'
    ];
  }



  $tempstore->set('isinit', false);

  return $form;
}

  /*
   *
   * {@inheritdoc}
   *
   * TODO: Impletment form validation here
   **/
  public function validateForm(array &$form, FormStateInterface $form_state) {
}
  /*
   * {@inheritdoc}
   **/
  public function submitForm(array &$form, FormStateInterface $form_state) {
  /*
   * We use ajax on this form, so this function is empty
   */
  }

 /*
  * Ajax callback function
  */
  public function getPlotData(array $form, FormStateInterface $form_state) {
    \Drupal::logger('metsis_ts_bokeh')->debug('Ajax callback y-axis: ' . $form_state->getValue('y_axis'));
    //Get data resource url from tempstore
     $tempstore = \Drupal::service('tempstore.private')->get('metsis_ts_bokeh');
     $data_uri = $tempstore->get('data_uri');

    //Get plot json data
    $items = adc_get_ts_bokeh_plot($data_uri, $form_state->getValue('y_axis'));
    //Create ajax response and add javascript
    $response = new AjaxResponse();
    $response->addCommand(
      new HtmlCommand(
        '.plot-container',
        '<div id="tsplot"><script>Bokeh.embed.embed_item(' . $items . ')</script></div>'),
      );
    return $response;

  }

}
