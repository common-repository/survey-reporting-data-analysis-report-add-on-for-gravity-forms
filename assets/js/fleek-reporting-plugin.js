const flgr_reporting_plugin = document.querySelector('[data-flgr-reporting-plugin]');
const flgr_data = {
  repeater_element: null,
  repeater_field_row_element: null,
  active_form: null,
  repeater_index: 0,
  answer_results: [],
  queue_ajax_load_amount: 10, //This should match queue_ajax_load_until_index 
  queue_ajax_load_until_index: 10, //This should match queue_ajax_load_amount
};

//IE alert
let flgr_ua = window.navigator.userAgent;
let flgr_isIE = /MSIE|Trident/.test(flgr_ua);
if(flgr_isIE){
  flgr_reporting_plugin.querySelector('.flgr-plugin-container').innerHTML = '<div class="flgr-w-100 flgr-panel flgr-bg-red flgr-box">Unfortunately, this plugin does not support IE. To upgrade to the latest browser, please see: <a href="https://www.microsoft.com/en-us/edge" target="_blank">https://www.microsoft.com/en-us/edge</a></div>';
}

//Data Repeaters
flgr_reporting_plugin.querySelectorAll('[data-flgr-repeater]')?.forEach((repeater) => {
  //Wrapper
  let wrapper = document.createElement('div');
  repeater.parentNode.insertBefore(wrapper, repeater);
  wrapper.appendChild(repeater);
  wrapper.classList.add('flgr-repeater-wrapper');
  wrapper.setAttribute('data-flgr-repeater-wrapper', "");
  wrapper.insertAdjacentHTML('beforeend', '<div data-flgr-repeater-content></div>');
  
  //Action buttons
  repeater.insertAdjacentHTML('beforeend', `
    <div class="flgr-repeater-actions">
      <button class="flgr-repeater-add-row" data-flgr-add-and-row><span class="plus">+</span><span class="condition-text">AND Condition</span></button>
      <button class="flgr-repeater-remove-row" data-flgr-remove-and-row style="display:none;">X</button>
    </div>
  `);
  repeater.querySelector('[data-flgr-row-criteria]').insertAdjacentHTML('afterbegin', `
    <button class="flgr-repeater-add-or-row" data-flgr-add-or-row><span class="plus">+</span><span class="condition-text">OR Condition</span></button>
  `);
  repeater.querySelector('[data-flgr-or-field-repeater]').insertAdjacentHTML('beforeend', `
    <button class="flgr-repeater-remove-or-row" data-flgr-remove-or-row style="display:none;">X</button>
  `);
  repeater.setAttribute('data-repeater-index', flgr_data.repeater_index);

  flgr_data.repeater_element = repeater.cloneNode(true);
  flgr_data.repeater_field_row_element = repeater.querySelector('[data-flgr-or-field-repeater]').cloneNode(true);
});

flgr_reporting_plugin.querySelectorAll('[data-flgr-ajax-submit]')?.forEach((button) => {
  button.removeAttribute('disabled');
});

//User agent data for contact form
flgr_reporting_plugin.querySelector('[data-flgr-additional-info="user_agent"]').innerHTML = navigator.userAgent;
flgr_reporting_plugin.querySelector('[data-flgr-additional-input-info="user_agent"]').value = navigator.userAgent;

//Event listeners
flgr_reporting_plugin.addEventListener('click', (event) => {
  //Refresh one answer
  if(event.target.closest('[data-flgr-refresh]')){
    event.preventDefault();
    
    let answers_element = event.target.closest('[data-form-id]').querySelector('[data-section-answers]');
    if(!answers_element){
      return false;
    }
    if(answers_element.hasAttribute('data-processing')){
      answers_element.removeAttribute('data-processing');
    }
    if(answers_element.hasAttribute('data-processed')){
      answers_element.removeAttribute('data-processed');
    }
    answers_element.innerHTML = 'Loading answers...';
    answers_element.setAttribute('data-process-queue', true);
    flgr_populate_answers();
  }
  
  //AJAX form submission for getting answers after selecting a form
  if(event.target.hasAttribute('data-flgr-ajax-submit')){
    event.preventDefault();
    switch(event.target.dataset.flgrAjaxSubmit){
      case 'generate_report':
        let question_answers = '';

        if(!flgr_data.active_form){
          return false;
        }

        flgr_data.active_form.fields.forEach(field => {
          if(field.displayOnly || field.type == "consent"){
            return;
          }
          question_answers += `
            <div class="flgr-question-answer" data-question-answer data-form-id="${field.formId}" data-field-id="${field.id}" data-field-type="${field.type}">
              <header class="flgr-results-header flgr-question-header">
                ${field.label} <span class="flgr-gform-field-identifier">(Field ID: ${field.id}) <button data-flgr-refresh><span class="dashicons dashicons-update"></span></button></span>
              </header>
          
              <section class="flgr-answers" data-section-answers>
                Loading answers...
              </section>
            </div>
          `;
        });

        document.querySelector('[data-flgr-toggle-display="results"]').style.display = 'block';

        flgr_reporting_plugin.querySelector('[data-flgr-ajax-content="report_results"]').innerHTML = `
    <header class="flgr-results-header">
      <h2>Questions &amp; Answers</h2>
    </header>

    <hr class="flgr-hr" />

    <div class="flgr-question-answers">
      ${question_answers}
    </div>
        `;

        flgr_reporting_plugin.querySelector('[data-export-db]')?.classList.remove('hide');

        flgr_setup_process_queue();
      break;

      case 'contact_us':
        let form = event.target.closest('form');
        if(!form){
          alert('Form could not be found');
          return false;
        }

        let data = new FormData(form);
        data.append('action', 'flgr_contact');

        fetch(ajaxurl, {
          method: 'post',
          body: data,
        })
        .then(response => response.json())
        .then(response => {
          if(!response.success){
            alert(response.message);
            return false;
          }
          flgr_reporting_plugin.querySelector('[data-contact-us-modal-content]').innerHTML = `<h2>Contact Us</h2><p>Thank you for your feedback</p>`;
        });
      break;
    }
  }

  //Report export to CSV
  if(event.target.closest('[data-export-db]')){
    event.preventDefault();

    flgr_reporting_plugin.querySelector('[data-slide-toggle-progress-bar]').style.maxHeight = '200px';

    let percent = 5;
    let creating_csv = false;
    const export_interval = setInterval(() => {
      const progress_bar_description = flgr_reporting_plugin.querySelector('[data-export-progress] [data-progress-description]');
      
      percent += Math.floor(Math.random() * 5) + 3;
      flgr_update_progress_bar('[data-export-progress]', percent);
      
      let form = document.querySelector('[data-flgr-ajax-event="report_results"]');
      let data = new FormData(form);
      data.append('flgr_AJAX', true);
      data.append('flgr_export_data', true);
      data.append('fldAnswerData', JSON.stringify(flgr_data.answer_results));
      
      if(percent <= 30){
        progress_bar_description.innerHTML = 'Status: Collating data';
      } else {
        progress_bar_description.innerHTML = 'Status: Creating CSV';
      }

      if(percent > 90){
        clearInterval(export_interval);
      }

      if(creating_csv){
        return false;
      }

      creating_csv = true;
      fetch(form.getAttribute('action'), {
        method: 'post',
        body: data,
      })
      .then(response => response.json())
      .then(response => {
        if(!response.success){
          return false;
        }

        progress_bar_description.innerHTML = 'Status: CSV generated, preparing download';
        flgr_update_progress_bar('[data-export-progress]', 100);

        clearInterval(export_interval);
        setTimeout(() => {
          progress_bar_description.innerHTML = `Status: CSV generated - <a href="${response.url}" target="_blank" data-download-export-csv>click here to download CSV</a>`;
          // window.open(response.url, '_blank');
        }, 1000);
      });
    }, 500);
  }

  //Exporting CSV progress bar
  if(event.target.hasAttribute('data-download-export-csv')){
    flgr_reporting_plugin.querySelector('[data-slide-toggle-progress-bar]').style.maxHeight = '0px';
    flgr_reporting_plugin.querySelector('[data-export-progress] [data-progress-description]').innerHTML = 'Status: Collating data';
    flgr_update_progress_bar('[data-export-progress]', 5);
  }

  //Modals
  if(event.target.hasAttribute('data-modal-popup')){
    event.preventDefault();
    let modal = document.getElementById(event.target.dataset.modalPopup);
    if(!modal){
      return false;
    }
    modal.classList.add('flgr-open-modal');
  }

  if(event.target.hasAttribute('data-hidden-field-type')){
    let modal = document.getElementById(event.target.dataset.modalPopup);
    if(!modal){
      return false;
    }
    modal.querySelector('[data-hidden-field-type]').value = event.target.dataset.hiddenFieldType;
  }

  // When the user clicks on <span> (x), close the modal
  if(event.target.hasAttribute('data-close-modal')){
    let modal = event.target.closest('.modal');
    modal.classList.remove('flgr-open-modal');
  }
});
// When the user clicks anywhere outside of the modal, close it
window.onclick = (event) => {
  let modal = document.querySelector('.flgr-open-modal');
  if (event.target == modal) {
    modal.classList.remove('flgr-open-modal');
  }
};

//Plugin event listener
flgr_reporting_plugin.addEventListener('change', (event) => {
  //Form selection
  //Will toggle the criteria view
  if(event.target.hasAttribute('data-flgr-form-select')){
    document.querySelector('[data-flgr-toggle-display="results"]').style.display = 'none';
    if(event.target.value !== ""){
      document.querySelector('[data-flgr-toggle-display="settings-footer"]').style.display = 'block';
      flgr_generate_repeater_fields(event.target.value);
    } else {
      document.querySelector('[data-flgr-toggle-display="settings-footer"]').style.display = 'none';
    }
  }

  //Field selection
  if(event.target.hasAttribute('data-flgr-gform-field')){
    if(!flgr_data.active_form){
      return false;
    }

    //Loop through all fields and figure out the search criteria available
    flgr_data.active_form.fields.forEach(field => {
      //parseInt floors the decimal place to get the question ID for surveys
      if(parseInt(event.target.value) != field.id){
        return;
      }

      let index = event.target.closest('[data-flgr-repeater]').dataset.repeaterIndex;

      let gform_value_element = event.target.closest('[data-flgr-or-field-repeater]').querySelector('[data-flgr-gform-value-block]');
      let old_value = gform_value_element.querySelector('[data-flgr-gform-value]').value;
      let gform_value_html = `<input type="text" name="fldSearchCriteria[value][${index}][]" value="${old_value}" data-flgr-gform-value />`;

      switch(field.type){
        case "select":
        case "checkbox":
        case "radio":
          gform_value_html = `
            <select name="fldSearchCriteria[value][${index}][]" data-flgr-gform-value>
              <option value="">Please select a choice</option>
          `;
          field.choices.forEach(choice => {
            gform_value_html += `
              <option>${choice.text}</option>
            `;
          });
          gform_value_html += `
            </select>
          `;
        break;
        case "survey":
          gform_value_html = `
            <select name="fldSearchCriteria[value][${index}][]" data-flgr-gform-value>
              <option value="">Please select a choice</option>
          `;
          field.inputs.forEach(input => {
            if(input.id != event.target.value){
              return;
            }
            field.choices.forEach(choice => {
              gform_value_html += `
                <option value="${input.name}:${choice.value}">${choice.text}</option>
              `;
            });
          });
          gform_value_html += `
            </select>
          `;
        break;
      }
      gform_value_element.innerHTML = `<label>Value:</label>${gform_value_html}`;
    });
  }
});

//Window event listners
addEventListener('scroll', flgr_setup_process_queue);
addEventListener('load', () => {
  document.querySelector('[data-form-select]').style.display = 'block';
});

//Functions
/**
 * Generates the select box repeater field for the search criteria
 * @param Integer form_id 
 * @returns false on failure otherwise void
 */
function flgr_generate_repeater_fields(form_id){
  if(
    typeof flgr_form_data === "undefined" ||
    flgr_form_data.length < 1
  ){
    return false;
  }

  if(!form_id){
    return false;
  }

  let active_form;
  flgr_form_data.forEach(form => {
    if(form.id == form_id){
      active_form = form;
    }
  });

  if(!active_form){
    return false;
  }
  flgr_data.active_form = active_form;

  let select_field_options_html = '<option value="">Please select a field</option>';
  active_form.fields.forEach(field => {
    if(field.displayOnly == true) {
      return;
    }

    let description = (field.description.length > 50 ? field.description.substr(0,47) + '...' : field.description);
    let option_label = field.label;

    switch(field.type){
      default:
        if(description !== ''){
          option_label += ' (Brief description: ' + description + ')';
        }
        select_field_options_html += `<option value="${field.id}">${option_label}</option>`;
      break;
      case 'survey':
        if(!field.input){
          break;
        }
        field.inputs.forEach(input => {
          select_field_options_html += `<option value="${input.id}">${option_label} ${input.label}</option>`;
        });
      break;
    }

  });
  document.querySelectorAll('[data-flgr-gform-field]').forEach(select => {
    if(!select.querySelector('option')){
      select.innerHTML = select_field_options_html;
    }
  });
}

/**
 * Check if the element is within the viewport of the browser
 * @param Object el 
 * @returns true/false
 */
function flgr_is_element_in_viewport(el){
  let rect = el.getBoundingClientRect();

  return (
    rect.top >= 0 &&
    rect.left >= 0 &&
    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
  );
}

/**
 * Sets up the queuing process for retreiving the answers via AJAX
 */
function flgr_setup_process_queue(){
  document.querySelectorAll('[data-section-answers]')?.forEach((answer, index) => {
    if(index >= flgr_data.queue_ajax_load_until_index){
      return false;
    }
    answer.setAttribute('data-process-queue', true);
  });

  document.querySelectorAll(`[data-question-answer]:nth-child(${flgr_data.queue_ajax_load_amount}n-${Math.ceil(flgr_data.queue_ajax_load_amount*0.75)})`)?.forEach((answer, index) => {
    if(flgr_is_element_in_viewport(answer)){
      flgr_data.queue_ajax_load_until_index = ((index+2) * flgr_data.queue_ajax_load_amount);
    }
  });

  flgr_populate_answers();
}

/**
 * Populates the answer box data with the data found from AJAX
 */
function flgr_populate_answers(){
  let form = document.querySelector('[data-flgr-ajax-event="report_results"]');

  document.querySelectorAll('[data-section-answers]')?.forEach((answer) => {
    if(
      // !flgr_is_element_in_viewport(answer) || 
      !answer.getAttribute('data-process-queue') ||
      answer.getAttribute('data-processing') ||
      answer.getAttribute('data-processed')
    ){
      return false;
    }

    let data = new FormData(form);
    let field_id = answer.closest('[data-field-id]').dataset.fieldId;
    answer.setAttribute('data-processing', true);
    data.append('action', 'flgr_get_answers');
    data.append('fldFieldId', field_id);

    fetch(ajaxurl, {
      method: 'post',
      body: data,
    })
    .then(response => response.json())
    .then(response => {
      const emit_event = new CustomEvent('flgr_ajax_answer_response', {detail: {answer: answer, ajax_response: response}});
      flgr_reporting_plugin.dispatchEvent(emit_event);
      if(response.success){
        answer.removeAttribute('data-processing');
        answer.removeAttribute('data-process-queue');
        answer.setAttribute('data-processed', true);
        answer.innerHTML = response.html;
      } else {
        answer.innerHTML = 'This field has responded with an unsuccessful response. Please feel free to <a href="#" class="flgr-link" data-modal-popup="contact-us-modal">contact the plugin developer</a> and report this issue with as much information as possible. Thank you for using this plugin!';
      }
    });
  });
}

/**
 * Updates the progress bar to percent amount passed
 * @param Object selector 
 * @param Interger percent 
 */
function flgr_update_progress_bar(selector, percent){
  const progress_bar_box = flgr_reporting_plugin.querySelector(`${selector} [data-progress-wrapper] [data-progress-box]`);
  const progress_bar_number = flgr_reporting_plugin.querySelector(`${selector} [data-progress-number]`);
  
  progress_bar_box.style.width = `${percent}%`;
  progress_bar_number.innerHTML = percent;
}