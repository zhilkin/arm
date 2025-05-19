/* 
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/ClientSide/javascript.js to edit this template
 */

// Для отображения нулевых объектов 
function CollapseRows(class_id) {
    var row = document.getElementById('#' + class_id);
    console.log(row);
    var is_collapsed = row.getAttribute("rows-expanded") === "false" ? true : false;
    console.log(row.getAttribute("rows-expanded"));
    row.setAttribute("rows-expanded", is_collapsed);
    console.log(row.getAttribute("rows-expanded"));
    const collection_sub_rows = document.getElementsByClassName(class_id);
    for (let i = 0; i < collection_sub_rows.length; i++) {
        if (is_collapsed === true) {
            collection_sub_rows[i].setAttribute("show-row", "true");
        } else {
            collection_sub_rows[i].setAttribute("show-row", "false");
        }
    }
}
function ShowZeroARMs() {
    var checkBox = document.getElementById("flexSwitchCheckChecked");
    const collection_subj = document.getElementsByClassName("zero_arm_subj");
    for (let i = 0; i < collection_subj.length; i++) {
        if (checkBox.checked === true) {
            collection_subj[i].setAttribute("hide-arm", "true");
        } else {
            collection_subj[i].setAttribute("hide-arm", "false");
        }
    }
    const collection = document.getElementsByClassName("zero_arm");
    for (let i = 0; i < collection.length; i++) {
        if (checkBox.checked === true) {
            collection[i].setAttribute("hide-arm", "true");
        } else {
            collection[i].setAttribute("hide-arm", "false");
        }
    }
}

