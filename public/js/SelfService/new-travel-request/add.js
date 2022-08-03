

function travelTypeForAdvance() {
    var index = 1;
  var traveltype = $('#travelType').val();
  // alert(traveltype);
  if (traveltype == 'LTR') {
    $('.international').empty();
      $('.domestic').append(` 
          <div class="col-sm-4">
            <div class="form-group">
            <label for="requestedAmount">Advance Amount</label>
            <input type="text" placeholder = "NPR" id="form-requestedAmount" name="requestedAmount" class="form-control" value="" >
            </div>
           </div>
          <div class="col-sm-4">
            <div class="form-group">
            <label for="file">Upload Files</label>
            <input type="file" id="filesUpload" name="files[]" class="form-control" multiple>
            <label for="file"><i>Maximum file size 8MB and supports doc,txt,pdf,jpg,png,docx,odt format.</i></label>
            </div>
          </div>`);
  } else {
    $('.domestic').empty();
    $('.international').append(`
    <div class="col-sm-4">
    <div class="form-group">
        <div class="row">
            <div class="col-md-4">
            <label for="associateName">Foreign Currency Type</label>
                 <br>
                <select class='currency form-control' name='currency' > </select> <br>
                <input type="hidden" id="countnote" value="1"> <br>
                <label for="associateName">Conversion Rate </label><br>
                <input type="number" id="conversionRate" value="1" name="conversionrate" class="form-control"> <br>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <table id='currencyDetail' class="table table-bordered">
                        <thead>
                            <tr>
                                <th>
                                    Note
                                </th>
                                <th>
                                    Quantity
                                </th>
                                <th>
                                Amount
                                </th>
                                <th>
                                    Action
                                </th>     
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="nnumber" name="fnote[]"  class="form-control fnote" data-id="1" id="fnote_1" >
                                </td>
                                <td>
                                    <input type="nnumber" name="fqty[]" class="form-control fqty" data-id="1" id="fqty_1">
                                </td>
                                <td>
                                <input type="nnumber" name="famount[]" id="famount_1" class="form-amount form-control famount" disabled>
                                </td>
                                <td>
                                    <button type="button"  class="btn btn-success addNoteDenom" id="sacasxas_1"><i class="fa fa-plus"></i></button> 
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                </div>
               
                
                <label for="associateName"> Converted Amount In NPR </label>
                <input type="text" name="advan" id="camount" class="form-control" disabled><br>
            </div>
        </div>
        <label>Advance Amount</label>
        <input type="text" class="form-requestedAmount form-control" name="requestedAmount" id="form-requestedAmount" min="0",step="0.01" readonly>
    </div>
</div>
      <div class="col-sm-4" style="margin-left:3rem">
        <div class="form-group">
        <label for="file">Upload Files</label>
        <input type="file" id="filesUpload" name="files[]" class="form-control" multiple>
        <label for="file"><i>File should be below 8MB and supports doc,txt,pdf,jpg,png,docx,odt format.</i></label>
        </div>
      </div>
    `);


        all_data=document.currencyList;
        app.populateSelect($('.currency'), all_data, 'code', 'code', '-select-',null, 1, true);

        // function addNoteDenom() {
          $('#currencyDetail').on('click', '.addNoteDenom', function () {
            index += 1;
            var html = '';
            html += '<tr>';
            html += '<td>';
            html += '<input type="nnumber" name="fnote[]"  class="form-control fnote" data-id="' + index + '" id="fnote_' + index + '">';
            html += ' </td>';
            html += '<td>';
            html += '<input type="nnumber" name="fqty[]"  class="form-control fqty" data-id="' + index + '" id="fqty_' + index + '">';
            html += '</td>';
            html += '</td>';
            html += '<td>';
            html += '<input type="nnumber" name="famount[]" id="famount_' + index + '" class=" form-control famount" disabled>';
            html +='</td>';  
            html +='<td>'; 
            html +='<input class="dtlDelBtn btn btn-danger" type="button" value="Del -" style="padding:3px;">'; 
            html +='</td>'; 
            html +='</tr>';       
          $('#currencyDetail tbody').append(html);
        });
      
      $(document).on('change keyup', '.fnote, .fqty', function () {
          calculateTotal();
          TotalAmount(this);
      });

    //   $(document).on('change keyup', '.fnote, .fqty', function () {
    //         totalfAmount();
    //     });
   
      function TotalAmount(t){
        var id = $(t).attr("data-id");
        var fnote = $("#fnote_"+id).val();
        var qty = $("#fqty_"+id).val();
        if(fnote == undefined || fnote == null || fnote == ""){
            fnote = 0;
        }
        if(qty==undefined||qty==null||qty==""){
            qty = 0;
        }
        var amount = eval(fnote) * eval(qty);
        $('#famount_'+id).val(amount);
      }
        // function totalfAmount(){
        //     const fTotalNote = document.getElementsByClassName('fnote');
        //     const fTotalNoteArr = [...fTotalNote].map(input => input.value);
        //     const fTotalQty = document.getElementsByClassName('fqty');
        //     const fTotalQtyArr = [...fTotalQty].map(input => input.value);  
        //     var famount=0;
        //     famount=fTotalQtyArr*fTotalNoteArr;
        //     $('#amount').val(famount);
        // }
        
        function calculateTotal(){
          const fTotalNote = document.getElementsByClassName('fnote');
          const fTotalNoteArr = [...fTotalNote].map(input => input.value);
          const fTotalQty = document.getElementsByClassName('fqty');
          const fTotalQtyArr = [...fTotalQty].map(input => input.value);
          
          var fTotal = 0;
          
          for(var i = 0; i<fTotalNote.length; i++){
              fTotal += fTotalNoteArr[i] *  fTotalQtyArr[i];
          }
          var nprTotal = 0;
          nprTotal = fTotal * $('#conversionRate').val();
          $('#form-requestedAmount').val(nprTotal);
          $('#camount').val(nprTotal);
      }
      
      $('#currencyDetail').on('click', '.dtlDelBtn', function () {
          var selectedtr = $(this).parent().parent();
          selectedtr.remove();
          calculateTotal();
      });
      $('.international').on('change', '#conversionRate', function () {
        calculateTotal();
        });
    //   function conversionRatetyudc() {
    //       var fcurr = $('#famount').val();
    //       var conv = $('#conversionRate').val();
    //       var amount = fcurr * conv ;
    //       $('#form-advanceAmount').val(amount);
    //       $('#camount').val(amount);
    //   }
  }
}
