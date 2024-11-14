document.addEventListener("DOMContentLoaded",function(){let e=document.getElementById("toggleButton");document.getElementById("toggleContent");let t=document.getElementById("toggleButtonPrev"),o=document.querySelectorAll(".zm-page-content-failed-log-filter-list-item");function r(){var e=document.getElementById("previous");e&&e.remove();let t=document.getElementById("form-container"),o=document.querySelectorAll(".zm-page-content-failed-log-filter-list-item"),r=document.getElementById("data-table");if(t||r){if(r)o.forEach(e=>{e.addEventListener("click",function(){o.forEach(e=>e.classList.remove("active")),this.classList.add("active");let e=this.getAttribute("data-filter");!function e(t){let o=`fetch-data.php?filter=${encodeURIComponent(t)}`;console.log(`Fetching data from URL: ${o}`);{console.log("filter:",t);let r=`${ajaxurl}?action=get_mailagents_count&filter=${t}`;fetch(r,{method:"GET"}).then(e=>{if(console.log("Response received from fetch."),!e.ok)throw console.error(`HTTP error! Status: ${e.status}`),Error(`HTTP error! Status: ${e.status}`);return e.json()}).then(e=>{if(console.log("Data received:",e),e.success){let t=e.data;if(console.log("Data received:",e),!Array.isArray(t)||0===t.length){console.warn("No data found or data is not an array:",t);return}let o=document.getElementById("data-table");if(!o){console.error("Table element not found");return}o.innerHTML="";var r="";t.forEach(e=>{console.log("Processing item:",e),r+=`<tr id="row-<?php echo $row['id']; ?>">
                                        <td>
                                            <div class="zm-page-content-table-row-checkbox">
                                                <input type="checkbox">
                                            </div>
                                        </td>
                                        <td><span>${e.failed_at}</span></td>
                                        <td><span>${e.email_address}</span></td>
                                        <td><span>${e.email_subject}</span></td>
                                        <td><div>${e.email_body}</div></td>
                                        <td><span>${e.error_code}</span></td>
                                        <td><span>${e.error_description}</span></td>
                                        <td>
                                            <button class="retry-button no-border no-background" data-id="<?php echo $row['id']; ?>">
                                            <svg id="Layer_2" data-name="Layer 2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" style="width:16px;" fill="#264DED">
                                            <path d="M2,8a.5.5,0,1,1-1,0A7,7,0,0,1,13,3.12V1.55a.5.5,0,0,1,1,0v3a.5.5,0,0,1-.5.49h-3a.5.5,0,0,1,0-1h2A6,6,0,0,0,2,8Zm12.41-.5A.5.5,0,0,0,14,8,6,6,0,0,1,3.54,12h2a.5.5,0,0,0,0-1h-3a.5.5,0,0,0-.5.49v3a.5.5,0,0,0,1,0V12.88A7,7,0,0,0,15,8a.5.5,0,0,0-.5-.5Z"/></svg>
                                            </button>
                                        </td>
                                        <td>
                                            <button class="delete-button no-border no-background" data-id="<?php echo $row['id']; ?>">
                                            <div class="zm-page-content-trash-icon"><?xml version="1.0" encoding="utf-8"?>
                                            <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                viewBox="0 0 14 14" style="width:16px;" fill="#777777" style="enable-background:new 0 0 14 14;" xml:space="preserve" fill="#777777">
                                            <path d="M12.5,2H10V1.6C10,0.7,9.3,0,8.5,0H5.5C4.7,0,4,0.7,4,1.6V2H1.5C1.2,2,1,2.2,1,2.5S1.2,3,1.5,3H2v8.8C2,13.1,3.3,14,4.4,14
                                            h5.1c1.1,0,2.4-0.9,2.4-2.2V3h0.5C12.8,3,13,2.8,13,2.5S12.8,2,12.5,2z M5,1.6C5,1.3,5.2,1,5.5,1h2.9C8.8,1,9,1.3,9,1.6V2H5V1.6z
                                                M11,11.8c0,0.7-0.8,1.2-1.4,1.2H4.4C3.8,13,3,12.4,3,11.8V3h8V11.8z M5,9.5v-4C5,5.2,5.2,5,5.5,5S6,5.2,6,5.5v4
                                            C6,9.8,5.8,10,5.5,10S5,9.8,5,9.5z M8,9.5v-4C8,5.2,8.2,5,8.5,5S9,5.2,9,5.5v4C9,9.8,8.8,10,8.5,10S8,9.8,8,9.5z"/>
                                            </svg>
                                            </div>
                                            </button>
                                        </td>
                                    </tr>  
                                `}),o.innerHTML=r}else alert("Failed to filter data.")}).catch(e=>{console.error("Error:",e)})}}(e)})});else if(t){if(t.innerHTML="",console.error("item length:",tempData.length),$i=1,tempData.length<1){tempData.push({index:tempData.length+1,fromName:"",fromEmail:"",token:""});let a=document.createElement("div");a.classList.add("form-row-group-wrapper"),a.innerHTML=`
                <div class="form-row-group">
                    <div>
                        <input type="text" name="transmail_from_name_1" value="" placeholder="Enter from address" class="form--input" onchange="updateTempData(0 , 'fromName', this.value)"/>
                    </div>
                    <div>
                        <input type="text" name="transmail_from_email_id_1" value="" placeholder="Enter from address" class="form--input" onchange="updateTempData(0, 'fromEmail', this.value)" />
                    </div>
                    <div>
                        <input type="password" name="transmail_send_mail_token_1" value="" placeholder="Enter mail agent token" class="form--input" onchange="updateTempData(0, 'token', this.value)" />
                    </div>
                </div>
                <div class="form-row-group-default">Default</div>
            `,t.appendChild(a)}else tempData.forEach((e,o)=>{let r=document.createElement("div");r.classList.add("form-row-group-wrapper");let a="",n="",l="";document.getElementById("row-"+e.id),console.log("row default: ",e.isDefault),e.reason,void 0!=e.isConnected&&(e.isConnected?n=`
                                        <div class="icon-wrapper">
                                        <img src="${transmailPluginData.plugin_url}/assets/images/circle.png" width="17" height="17" title="Connection successful!">
                                    </div> 
                                        `:(n=`
                                        <div class="icon-wrapper">
                                        <img src="${transmailPluginData.plugin_url}/assets/images/attencion.png" width="17" height="17" title="Connection Failed: ${e.reason}">
                                </div> 
                                 `,l=`Connection Failed: ${e.reason}`)),a=e.isDefault?`
                            <div class="form-row-group-default">Default</div>
                        `:`
                                <div class="form-row-group-actions" data-index="${$i-1}" onclick="makeDefault('${$i-1}')" >
                                <button type="button" class="form-row-group-actions-button" style="
                                color: #264DED;">Make a default</button>
                                </div>
                                <div class="form-row-group-actions" data-index="${$i-1}" onclick="deleteField('${$i-1}')" >
                            <i class="form-row-group-actions-separator"></i>
                            <button type="button" class="form-row-group-actions-button" style="color: red;">Delete</button>
                                </div>
                            `,r.innerHTML=`
                    ${n}   
                    <div class="form-row-group form-row-group--without-gap">
                            <div style="display: flex; gap: 12px;">
                                <div>
                                    <input type="text" name="transmail_from_name_${$i}" value="${e.fromName}" placeholder="Enter from address" class="form--input" onchange="updateTempData(${$i-1} , 'fromName', this.value)"/>                              
                                
                                </div>
                                <div>
                                    <input type="text" name="transmail_from_email_id_${$i}" value="${e.fromEmail}" placeholder="Enter from address" class="form--input" onchange="updateTempData(${$i-1}, 'fromEmail', this.value)" />
                                </div>
                                <div>
                                    <input type="password" name="transmail_send_mail_token_${$i}" value="${e.token}" placeholder="Enter mail agent token" class="form--input" onchange="updateTempData(${$i-1}, 'token', this.value)" />
                                </div>
                            </div>
                            <p style="
                                margin-block: 2px 0;
                                color: red;
                                max-width: 100%;
                                overflow: hidden;
                                text-overflow: ellipsis;
                                ">${l}</p>
                        </div>
                        ${a}
                        
                    `,$i+=1,t.appendChild(r)});let n=document.createElement("div");n.classList.add("form-row-group"),n.innerHTML=`
                            <div class="form-row-group-plus">
                                <button onclick="addEntry()" title="Add new mail agent" id="toggleButtonPrev" type="button" class="add-button no-border">
                                Add new
                                </button>
                            </div>
                        `,t.appendChild(n)}}}function a(){let e=document.querySelectorAll("tbody .zm-page-content-table-row-checkbox input:checked"),t=document.querySelector(".zm-page-content-table-header");if(t.innerHTML="",e.length>0){let o=document.createElement("tr");o.innerHTML=`
                <th class="delete-header">
                    <div class="zm-page-content-table-body-failed-log-selected">
                        <button class="no-border no-background" onclick="deleteSelectedRows()" style="width:16px">
                        <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 14 14" style="width:16px;" fill="#777777" xml:space="preserve">
                            <path d="M12.5,2H10V1.6C10,0.7,9.3,0,8.5,0H5.5C4.7,0,4,0.7,4,1.6V2H1.5C1.2,2,1,2.2,1,2.5S1.2,3,1.5,3H2v8.8C2,13.1,3.3,14,4.4,14h5.1c1.1,0,2.4-0.9,2.4-2.2V3h0.5C12.8,3,13,2.8,13,2.5S12.8,2,12.5,2z M5,1.6C5,1.3,5.2,1,5.5,1h2.9C8.8,1,9,1.3,9,1.6V2H5V1.6z M11,11.8c0,0.7-0.8,1.2-1.4,1.2H4.4C3.8,13,3,12.4,3,11.8V3h8V11.8z M5,9.5v-4C5,5.2,5.2,5,5.5,5S6,5.2,6,5.5v4C6,9.8,5.8,10,5.5,10S5,9.8,5,9.5z M8,9.5v-4C8,5.2,8.2,5,8.5,5S9,5.2,9,5.5v4C9,9.8,8.8,10,8.5,10S8,9.8,8,9.5z"/>
                        </svg>
                        </button>
                    </div>
                </th>
                <th><div><span>Delete (${e.length})</span>
                </div></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            `,t.appendChild(o)}else{let r=document.createElement("tr");r.innerHTML=`
                <th></th>
                <th>Time</th>
                <th>To address</th>
                <th>Subject</th>
                <th>Message</th>
                <th>Attachments</th>
                <th>Error code</th>
                <th>Error description</th>
                <th></th>
                <th>
                    <div class="zm-page-content-failed-log-filter">
                        <svg id="Layer_2" data-name="Layer 2" width="14" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16">
                            <defs><style>.cls-1{fill:none;}</style></defs>
                            <path id="_07" data-name=" 07" d="M14.05,1.06H2a1,1,0,0,0-.8,1.58L5.82,7.8a.82.82,0,0,1,.2.5V14a1,1,0,0,0,1,1,.7.7,0,0,0,.49-.2l2-1.58a1,1,0,0,0,.49-.9v-4c0-.2,0-.4.2-.5l4.66-5.16a1,1,0,0,0-.79-1.58ZM9.39,7.11h0A2,2,0,0,0,9,8.3v4H9L7,14V8.3a2,2,0,0,0-.4-1.19h0L2.05,2.05H14Z"/>
                            <rect class="cls-1" x="1" y="1" width="14" height="14"/>
                        </svg>
                        <div class="zm-page-content-failed-log-filter-list-wrapper">
                            <ul class="zm-page-content-failed-log-filter-list">
                                <li class="zm-page-content-failed-log-filter-list-item" data-filter="ALL">ALL</li>
                                <li class="zm-page-content-failed-log-filter-list-item" data-filter="SERR_123">SERR_123</li>
                                <li class="zm-page-content-failed-log-filter-list-item" data-filter="SERR_134">SERR_134</li>
                                <li class="zm-page-content-failed-log-filter-list-item" data-filter="SERR_144">SERR_144</li>
                            </ul>
                        </div>
                    </div>
                </th>
            `,t.appendChild(r)}}r(),o&&o.forEach(e=>{e.addEventListener("click",function(){console.log("inside filter");let e=this.getAttribute("data-filter"),t=document.querySelectorAll(".zm-page-content-table tbody tr");t.forEach(t=>{let o=t.querySelector("td:nth-child(7)").textContent;"ALL"===e||o===e?t.style.display="":t.style.display="none"})})}),window.validateForm=function(){console.log("validate Form tempData:",tempData),document.getElementById("tempDataCount").value=tempData.length,document.getElementById("tempData").value=tempData;let e=document.querySelectorAll("#form-container .form-row-group input"),t=/^[^\s@]+@[^\s@]+\.[^\s@]+$/;e.parentElement;let o=!0;return console.log("input length: ",e.length),e.forEach(e=>{let r=e.value.trim(),a=e.name;if(console.log("input value: ",r),console.log("input name: ",a),e.classList.remove("invalid-input"),""===r||a.includes("email")&&!t.test(r)){e.classList.add("invalid-input"),o=!1;return}}),"false"===o&&(alert("Please enter the valid data"),r()),o},window.updateTempData=function(e,t,o){if(console.log("Updating item at position:",e),e<0||e>=tempData.length){console.error("Invalid item position:",e);return}let r=tempData[e];console.log("Item to update:",r),r?(r[t]=o,console.log("Updated tempData:",tempData),document.getElementById("tempDataCount").value=tempData.length,document.getElementById("tempData").value=tempData):console.error("Item not found at position:",e)},document.querySelectorAll("tbody .zm-page-content-table-row-checkbox input").forEach(e=>{e.addEventListener("change",a)}),window.deleteSelectedRows=function(){let e=Array.from(document.querySelectorAll(".row-checkbox:checked")).map(e=>e.getAttribute("data-id"));if(0===e.length){alert("No rows selected for deletion.");return}confirm("Are you sure you want to delete the selected records?")&&fetch(ajaxurl,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:new URLSearchParams({action:"delete_selected_logs",ids:JSON.stringify(e),nonce:myAjax.nonce})}).then(e=>e.json()).then(t=>{t.success?e.forEach(e=>{document.getElementById("row-"+e).remove()}):alert("Failed to delete records.")})},console.log("script.js is loaded"),window.deleteField=function(e){console.log("inside the deleteField",e),e>=0&&e<tempData.length&&tempData.splice(e,1),console.log("after removed tempData: ",tempData),r()},window.makeDefault=function(e){console.log("inside the deleteField",e),tempData.forEach(function(e,t){e.isDefault=!1}),tempData[e].isDefault=!0,console.log("after removed tempData: ",tempData),console.log("makedefault tempData: ",e),document.getElementById("defaultData").value=parseInt(e)+1,r()},console.log("script.js is loaded"),window.addEntry=function(){tempData.length<3?(tempData.length&&tempData.map(e=>e.index),tempData.push({index:tempData.length+1,fromName:"",fromEmail:"",token:""})):alert("Mail agent limit exceeded"),r()},console.log("script.js is loaded"),window.deleteDiv=function(e){var t=e.closest(".form-row-group");t&&t.remove()},console.log("script.js is loaded"),e&&e.addEventListener("click",()=>{confirm("Are you sure you want to show this record?")&&fetch(`${ajaxurl}?action=get_mailagents_count`,{method:"GET",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"}}).then(e=>e.json()).then(e=>{void 0!==e.length?(console.error("data length: ",e.length),console.error("entryCount: ",1),addEntry()):console.error("Failed to fetch mail agents count:",e.error)}).catch(e=>{console.error("Error fetching mail agents count:",e)})}),t&&t.addEventListener("click",()=>{confirm("Are you sure you want to show this record?")&&fetch(`${ajaxurl}?action=get_mailagents_count`,{method:"GET",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"}}).then(e=>e.json()).then(e=>{void 0!==e.length?(console.error("data length: ",e.length),console.error("entryCount: ",1),addEntry()):console.error("Failed to fetch mail agents count:",e.error)}).catch(e=>{console.error("Error fetching mail agents count:",e)})}),window.deleteRow=function(e){var t=e;confirm("Are you sure you want to delete this record?")&&fetch(ajaxurl,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:new URLSearchParams({action:"delete_failed_email",id:t,nonce:myAjax.nonce})}).then(e=>e.json()).then(e=>{e.success?(alert("Record deleted successfully!"),document.querySelector(`#row-${t}`).remove()):alert("Failed to delete record: "+(e.data||"Unknown error"))}).catch(e=>{error_log("error while delte: ".error),alert("An error occurred while trying to delete the record.")})},document.querySelectorAll(".retry-button").forEach(function(e){e.addEventListener("click",function(e){e.preventDefault();var t=this.getAttribute("data-id");confirm("Are you sure you want to retry this record?")&&fetch(ajaxurl,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:new URLSearchParams({action:"retry_failed_email",id:t,nonce:myAjax.nonce})}).then(e=>(console.log(e,"retry response"),e.json())).then(e=>{console.log("inside data:".data),e.success?(alert("Retry email succeeded."),deleteRow(t)):alert("Failed to retry email: "+(e.data||"Unknown error"))}).catch(e=>{error_log("error while delte: ".error),alert("An error occurred while trying to delete the record.")})})}),window.deleteRow=function(e){var t=e;confirm("Are you sure you want to delete this record?")&&fetch(myAjax.ajaxurl,{method:"POST",headers:{"Content-Type":"application/x-www-form-urlencoded; charset=UTF-8"},body:new URLSearchParams({action:"delete_failed_email",id:t,nonce:myAjax.nonce})}).then(e=>e.json()).then(e=>{e.success?(alert("Record deleted successfully!"),document.querySelector(`#row-${t}`).remove()):alert("Failed to delete record: "+(e.data||"Unknown error"))}).catch(e=>{error_log("error while delte: ".error),alert("An error occurred while trying to delete the record.")})},document.querySelectorAll(".delete-button").forEach(function(e){e.addEventListener("click",function(){deleteRow(this.getAttribute("data-id"))})})});
