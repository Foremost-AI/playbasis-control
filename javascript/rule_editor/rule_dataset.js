var DEBUG = true;
//Dataset Draft
//###############################

//rule_dataset.js
DataSet = function(jsonArray, parent_id) {
  if(!jsonArray || !$.isArray(jsonArray)) {
    try {
        $.parseJSON(jsonArray);
    }
    catch(e) {
        console.log(e);
    }
    //console.log('Please initial data (json array)');
    if(DEBUG)console.log(jsonArray);


    return;
  }
  
  return {
    dataset: jsonArray,
    parent_id: parent_id,

    getHTML: function() {
      // console.log('getHtml')
      // console.log(jsonArray['tooltips'])

      //console.log('getHtml');
      // diery hack
      window.isValid = true;
 
      var _checkBool = function(string) {
          if(string == true) {
            return 'checked';
          }
          else if(string == false){
            return '';
          }
          return '';
      },

      _inputByType = function(v) {
        return {
          'hidden': '<input type="text" class="hide" value="'+v.value+'" />',

          'text': 
            '<input type="text" class="" placeholder="' + v.placeholder + '" value="'+v.value+'" maxlength="60" />',

          'number': 
            '<input type="text" class="input_number number" placeholder="' + v.placeholder + '" value="' + v.value + '" maxlength="20" />',

          'date': 
            '<input type="text" class="input-xlarge date datepickerx" id="date'+((new Date()).getMilliseconds()+1)+'" placeholder="' + v.placeholder + '" value="'+v.value+'" />',

          'time': 
            '<input type="text" class="input-xlarge time timepickerx" id="time'+((new Date()).getMilliseconds()+1)+'" placeholder="Time" value="' + v.value + '" />',

          'date_time': 
            '<input type="text" class="input-xlarge date_time datetimepickerx" id="date_time'+((new Date()).getMilliseconds()+1)+'" placeholder="Date - Time" value="'+v.value+'" />',

          'date_tween': 
            '<input type="text" class="input-xlarge date_tween datepickerx date_tween1" id="date0'+( (new Date()).getMilliseconds() +2)+'" /> '+
            'to <input type="text" class="input-xlarge datepickerx date_tween2" id="date0'+( (new Date()).getMilliseconds() +3)+'" />',

          'timeout':
            '<input type="text" class="timeout" value="' + v.value + '" />&nbsp;' +
                '<select id="pbd_interval_unit" name="interval_unit">' +
                '<option value="none">None</option>' +
                '<option value="second">Second</option>' +
                '<option value="day">Days</option>' +
                '</select>',

          'cooldown': 
            '<input type="text" class="cooldown" placeholder="Time in Second unit" value="' + v.value + '" />&nbsp;' +
              '<select id="pbd_time_unit">' +
                '<option value="sec">Second</option>' +
                '<option value="min">Minute</option>' +
                '<option value="hour">Hours</option>' +
                '<option value="day">Days</option>' +
                '<option value="forever">Forever</option>' +
              '</select>',

          'collection': 
            BadgeSet.getBadgeImage(v.value)+'<input type="text" class="collection reward_type hide" placeholder="'+v.placeholder+'" value="'+v.value+'" />&nbsp;' +
              '<span class="edit_reward_type btn btn-info btn-mini"><i class="icon-gift icon-white"></i></span>&nbsp;',

          'boolean': 
            '<input type="checkbox" class="input_boolean boolean" id="bool'+((new Date()).getMilliseconds()+1)+'" name="'+v.value+'" value="'+v.value+'" checked="'+_checkBool(v.value)+'" />',

          'read_only': 
            '<input type="text" class="read_only" placeholder="'+v.placeholder+'" value="'+v.value+'" />'
        };
      };

      var ruleAction = $('<span class="pbd_rule_action">')
                  .append($('<span class="btn btn-info btn-mini" id="pbd_rule_action_edit"><i class="icon-edit icon-white"></i></span>'))
                  .append($('<span class="btn btn-info btn-mini" id="pbd_rule_action_save" style="display: none;"><i class="icon-ok icon-white"></i></span>'));

      var jigsaw = $('<table class="table table-bordered">');

      // looping world
      $.each(jsonArray, function(k, v) {
          var row = $('<tr>').addClass('pbd_rule_param state_text parent_id_'+parent_id),
              labelColumn = $('<td>').addClass('pbd_rule_label'),
              dataColumn = $('<td>').addClass('pbd_rule_data');

        if(v.field_type === 'hidden') {
          row.addClass('hide');
        }

        // label properties
        labelColumn.addClass('field_type_' + v.field_type);
        labelColumn.addClass('sort_' + v.sortOrder);
        labelColumn.addClass('name_' + v.param_name);
        labelColumn.html(v.label);
        row.append(labelColumn);
        // end label properties

        //add info-hover into
        if(v['tooltips'] === undefined) {
          v['tooltips'] ='';
        }
        labelColumn.find('a').remove();
        labelColumn.append(
          $('<a>')
            .attr({
              'rel': 'tooltip',
              'data-original-title': v['tooltips']
            })
            .append(
              $('<i>')
                .attr('class', 'icon-question-sign icon-white help')
            )
        );

        // data properties
        // depend on data type
        var inputType = $(_inputByType(v)[v.field_type]);
        // assign vailidation rules here
        if(v.param_name === 'url') {
         inputType.addClass('url') ;
        }
        // console.log(inputType);

        ruleField = $('<span class="pbd_rule_field">').append(inputType);
        ruleText = $('<span class="pbd_rule_text view_as_' + v.field_type  +'">'+v.value+'</span>');

        dataColumn.append(ruleText);
        dataColumn.append(ruleField);
        
        // append edit button
        if(v.field_type === 'read_only') {
          dataColumn.append('<span class="pbd_rule_action">');
        }
        else {
          dataColumn.append(ruleAction.clone().addClass('parent_id_' + parent_id));
        }
        row.append(dataColumn);
        // end data properties

        jigsaw.append(row);
      });

      _validateByType = {
        'number': function(value) {
          console.log('hello from number validate : ' + value);
          console.log(/^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(value));
          return /^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(value);
        },
        'url': function(value) {
          //console.log('hello from url validate : ' + value);
          // can be null
          if( value == '') return true;

//          return /^(https?|s?ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(value);
            return true;
        },
        'required': function(value) {
          //console.log('hello from required validate : ' + value);
          return /^-?(?:\d+|\d{1,3}(?:,\d{3})+)?(?:\.\d+)?$/.test(value);
        },
        'valid': function(value) {
          //Prevent user input escape char and symbolic
          return value.search(/['"]/g) < 0;
        }
      };

      // *****************************//
      // binding event for input      //
      // *****************************//

      $('.pbd_rule_unit_wrapper').on('keyup', 'input', function(event) {
        // loop for each class type
        $.each(_validateByType, function(validatorName, validatorFunction) {
          var target = $(event.currentTarget);
          if(target.hasClass(validatorName) ) {
            if(DEBUG)console.log(validatorName);

            if(!validatorFunction(target.val())) {
              window.isValid = false;
              if(validatorName === 'valid') {
                $('#errorModal .modal-body').html('rule attribute can not contain these character \' "');
              }
              else {
                $('#errorModal .modal-body').html( validatorName + ' is not valid');
                console.log(validatorName + ' fail');
              }
            }
            else {
              window.isValid = true;
              //console.log(validatorName + ' pass');
            }
          }
        });
      });

      // *****************************//
      // binding edit/save button     //
      // *****************************//

      // Clear old binding before binding new one
      $('ul.pbd_rule_unit_wrapper').off('click', '.pbd_rule_action span');
      $('ul.pbd_rule_unit_wrapper').on('click', '.pbd_rule_action span', function(event) {
        event.stopPropagation();
        var $this = $(this),
            $thisrow = $this.parent().parent().parent(),
            rowText = $thisrow.find('.pbd_rule_text'),
            rowField = $thisrow.find('.pbd_rule_field');

        //move data from field to text
        $thisrow.toggleClass('state_text state_field');

        if(this.id === 'pbd_rule_action_edit') {
          $thisrow.find('.pbd_rule_action span#pbd_rule_action_edit').hide();
          $thisrow.find('.pbd_rule_action span#pbd_rule_action_save').show();
          $thisrow.find('.pbd_rule_data .pbd_rule_text').hide();
          $thisrow.find('.pbd_rule_data .pbd_rule_field').show();

          if($thisrow.find('.collection').length > 0) {
            // do nothing
            if(DEBUG)console.log('edit > collection');
            //Hide Text Field
              rowField.find('input').hide();
          }

          else if($thisrow.find('.cooldown').length > 0) {
            // do nothing
            if(DEBUG)console.log('edit > cooldown');
          }

          else if($thisrow.find('.date_tween').length > 0) {
            if(DEBUG)console.log('edit > date_tween');
            $thisrow.find('.pbd_rule_field input.date_tween1').val($thisrow.find('span.date_tween1_tx').html());
            $thisrow.find('.pbd_rule_field input.date_tween2').val($thisrow.find('span.date_tween2_tx').html());
          }

          else if($thisrow.find('.boolean').length > 0) {
            var boolInput = $thisrow.find('.input_boolean');
            boolInput.attr('checked', (rowText.html() === 'true'));
            if(DEBUG)console.log('edit > boolean');
          }
         
          else if($thisrow.find('.date_time').length > 0) {
            if(DEBUG)console.log('edit > date_time');
            rowField.find('input').val(rowText.html());
          }

          else if($thisrow.find('.time').length > 0) {
            if(DEBUG)console.log('edit > time');
          }

          else if($thisrow.find('.timeout').length > 0) {
              //incase of cool down cast value btw 'second' and other unit
              if(DEBUG)console.log('edit > timeout');
              var unit = $thisrow.parent().find(".name_interval_unit").parent().find(".pbd_rule_data .pbd_rule_text").html();

              $thisrow.find(".pbd_rule_data #pbd_interval_unit option[value='"+unit+"']").attr("selected","selected");
          }

          else {
            /*
             * Special case that never think before
             * WEEKLY and MONTHLY
             */
             var  $greatGrandParent = $thisrow.parent().parent().parent().parent().parent().parent(),
                  anotherType = $greatGrandParent.find('.name_only').html();

            if(anotherType.match('WEEKLY')) {
            // if(anotherType === 'CONDITION : WEEKLY') {
              if($('#weekday').length <= 0) {
                var weekday = {
                  'Sun': 'Sunday',
                  'Mon': 'Monday',
                  'Tue': 'Tuesday',
                  'Wed': 'Wednesday',
                  'Thu': 'Thursday',
                  'Fri': 'Friday',
                  'Sat': 'Saturday'
                }
                var weekoption = $('<select id="weekday">');
                $.each(weekday, function(key, value) {   
                   weekoption.append($('<option>', { value : key })
                    .text(value));
                });
                rowField.children().hide();
                rowField.append(weekoption);
              }
              $('#weekday').val(rowText.html());
              //console.log('WEEKLY');
            }
            else if(anotherType.match('MONTHLY')) {
              if($('#monthday').length <= 0) {
                var monthday = {
                   1:  '1',  2: ' 2',  3:  '3',  4:  '4',  5:  '5',  6:  '6',  7:  '7',  8:  '8',  9:  '9', 10: '10',
                  11: '11', 12: '12', 13: '13', 14: '14', 15: '15', 16: '16', 17: '17', 18: '18', 19: '19', 20: '20',
                  21: '21', 22: '22', 23: '23', 24: '24', 25: '25', 26: '26', 27: '27', 28: '28', 29: '29', 30: '30', 31: '31'
                }
                var monthoption = $('<select id="monthday">');
                $.each(monthday, function(key, value) {   
                   monthoption.append($('<option>', { value : key })
                    .text(value));
                });
                rowField.children().hide();
                rowField.append(monthoption);
              }
              $('#monthday').val(rowText.html());              
              //console.log('MONTHLY');
            }
            else {

              // default case
              if(DEBUG)console.log('edit > normal case');
              rowField.find('input').val(rowText.html());
            }
          }
          $thisrow.find('.pbd_rule_data .pbd_rule_field input[type="text"]').focus();
          $thisrow.find('.pbd_rule_data .pbd_rule_field input[type="text"]').select();
        }
        //  Action SAVE !!!!!
        else {
          if(!window.isValid) {
            // alert something
            $('#errorModal').modal({'backdrop': false});

            return;
          }          


          $thisrow.find('.pbd_rule_action span#pbd_rule_action_edit').show();
          $thisrow.find('.pbd_rule_action span#pbd_rule_action_save').hide();
          $thisrow.find('.pbd_rule_data .pbd_rule_text').show();
          $thisrow.find('.pbd_rule_data .pbd_rule_field').hide();

          // assume that pbd_rule_action_save
          if($thisrow.find('.collection').length > 0) {
            if(DEBUG)console.log('save > collection');
            

            //Set value from edit text to nornal text
            var key = rowField.find('input').val();
              rowText.html(key);

            //Append Badges Images here
              //Hide Text View
              rowText.hide();
              rowText.parent().find('img').remove();
              rowText.parent().prepend(BadgeSet.getBadgeImage(key))


          }

          else if($thisrow.find('.timeout').length > 0) {
              //incase of cool down cast value btw 'second' and other unit
              if(DEBUG)console.log('save > timeout');
              var unit = $thisrow.find('#pbd_interval_unit').val();
              var value = '';
              if(unit == 'day') {
                  value = unit;
              }
              else if(unit == 'second') {
                  value = unit;
              }
              else if(unit == 'none') {
                  value = 'second';
                  $thisrow.find('.timeout').val(0);
                  rowText.html(0);
              }
              else {
                  value = 'second';
              }

              rowText.html(rowField.find('input').val()+" "+value[0].toUpperCase() + value.slice(1));
              $thisrow.parent().find(".name_interval_unit").parent().find(".pbd_rule_data .pbd_rule_text").html(value);
              $thisrow.parent().find(".name_interval_unit").parent().find(".pbd_rule_data .pbd_rule_field").html('<input type="text" value="'+value+'">');
          }
          
          else if($thisrow.find('.idwithqty').length > 0) {
            if(DEBUG)console.log('save > idwithqty');           
            rowText.html(rowField.find('.reward_type').val());
          }

          else if($thisrow.find('.cooldown').length > 0) {
            //incase of cool down cast value btw 'second' and other unit
            if(DEBUG)console.log('save > cooldown');
            var unit = $thisrow.find('#pbd_time_unit').val();
            var value = '0';
            if(unit == 'min') {
              value = (rowField.find('input').val())*60;
            }
            else if(unit == 'hour') {
              value = (rowField.find('input').val())*60*60;
            }
            else if(unit == 'day') {
              value = (rowField.find('input').val())*60*60*24;
            }
            else if(unit == 'forever') {
              value = 3110400000 // 100 year;
            }
            else {
              value = (rowField.find('input').val());
            }
            $thisrow.find('.cooldown').val(value);
            rowText.html(value);
            $thisrow.find("#pbd_time_unit option[value='sec']").attr("selected","selected");
          }

          else if($thisrow.find('.date_tween').length > 0) {
            if(DEBUG)console.log('save > date_tween');
            rowText.html( 
              '<span class="date_tween1_tx sbtw">'+$thisrow.find('.date_tween1').val()
              +'</span> to <span class="date_tween2_tx sbtw">'
              +$thisrow.find('.date_tween2').val()
              +'</span>'
              );
          }

          else if($thisrow.find('.input_boolean').length > 0) {
            if(DEBUG)console.log('save > input_boolean');
            var boolInput = $thisrow.find('.input_boolean');
            if(boolInput.is(':checked')) {
              boolInput.val(true);
              rowText.html('true');
            }
            else {
              boolInput.val(false);
              rowText.html('false');
            }
          } 
          
          else if($thisrow.find('.date_time').length > 0) {
            //console.log('write html > ' + rowField.find('input').val());
            rowText.html(rowField.find('input').val());
          }


          else{
            /*
             * Special case that never think before
             * WEEKLY and MONTHLY
             */
             var  $greatGrandParent = $thisrow.parent().parent().parent().parent().parent().parent(),
                  anotherType = $greatGrandParent.find('.name_only').html();

            if(anotherType.match('WEEKLY')) {
              var val = $('#weekday').val();
              rowText.html(val);
              rowField.find('input')
                .val(val)
                .html(val);
              //console.log('save > WEEKLY');
            }
            else if(anotherType.match('MONTHLY')) {
              var val = $('#monthday').val();
              rowText.html(val);
              rowField.find('input')
                .val(val)
                .html(val);
              //console.log('save > MONTHLY');
            }
            else {
              // default case
              //console.log('save > hello' );
              rowText.html(rowField.find('input').val());
            }
            
          }

        }     
        // console.log(this.id);
      });

      // console.log(jigsaw[0].outerHTML);
      return jigsaw[0].outerHTML;
    },

    getJSON: function() {
      //console.log('getJson : ' + this.parent_id);
      var result = [];

      $('#'+this.parent_id+' tbody tr').each(function() {
        var obj = {}, 
            $this = $(this),
            class_data = $($(this).find('td')[0]).attr('class');

        data = class_data.split(' ');
        $.each(data, function(k, v){
          if(v.match('name_')) {
            obj.param_name = v.split('name_')[1];
          }
          if(v.match('field_type_')) {
            obj.field_type = v.split('field_type_')[1];
          }
          if(v.match('sort_')) {
            obj.sortOrder = v.split('sort_')[1];
          }
        });
        
        obj.label = $this.find('td')[0].innerHTML;
        obj.placeholder = $this.find('input').attr('placeholder');

        /* 
         * track to grand parent node to get node header 
         * for formatting result to correct format for backend
         */ 
        var value = $this.find('input').val(),
            $parent = $this.parent().parent().parent().parent().parent().parent(),
            anotherType = $parent.find('.name_only').html();

        switch(anotherType) {       
          case 'BEFORE_DATE':
            // Before: date time in unix timestamp
          case 'AFTER_DATE':
            // After: date time in unix timestamp
            try {
              value = Date.parse(value).getTime()/1000;
            }
            catch(e) {
              // value is unix time format so correct
              // skip the error
            }
            break;

          case 'BETWEEN':
            // Between :time in 24hr format {00:00 - 23:59}
            // do nothing
          case 'COOLDOWN':
            // Cooldown :cooldown time in second
            // do nothing            
          case 'DAILY':
            // Daily->time_of_day : time in 24hr format {00:00 - 23:59}
            // donothing
          case 'WEEKLY':
            // Weekly->time_of_day : time in 24hr format {00:00 - 23:59}
            // Weekly->date_of_month{1-7}
          case 'MONTHLY':
            // Monthly->time_of_day : time in 24hr format {00:00 - 23:59}
            // Monthly->date_of_month{1-31}
          case 'EVERY_N_DAY':
            // EveryDay->time_of_day : time in 24hr format {00:00 - 23:59}
            // EveryDay->num_of_day : num          
            break;
        }

        obj.value = value;
        result.push(obj);
      });
      //console.log(result);
      return result;
    }
  }
};



      // // *****************************//
      // // binding event hover table row//
      // // *****************************//
      // (function(){
      //   // toggle tooltips on 
      //   $('[rel=tooltip]').tooltip();
      // }());