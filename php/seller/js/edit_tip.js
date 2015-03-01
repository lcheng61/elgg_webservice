// Sets the min-height of #page-wrapper to window size
$(function() {

	var idea;
	var idea_id = getUrlParameter("tip_id");

	//$("#allowSpacesTags").tagit();

	console.log("idea_id=" + idea_id);
	if (idea_id != undefined && idea_id != null) { //edit data for teh prodcut. get the prodcut detail first of all.
		$('#page_title').text("Edit Idea");
		$('#idea_id').val(idea_id);
		getIdeaDetail();
	} //otehrwise, create a new prodcut.





	function getIdeaDetail() {
		var get_ideaurl = server + idea_get + "&tip_id=" + idea_id +
			'&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(get_ideaurl);

		$.getJSON(get_ideaurl, function(data) {
			console.log(JSON.stringify(data));
			if (data.status == 0) { //read prodcut detail successfully.
				console.log('tip title=' + data.result.tip_title);
				$('#cover_caption').html(data.result.tip_title);
				$('#category').val(data.result.tip_category);

				if (data.result.tip_tags != undefined) {
					//console.log("tags original = " + data.result.tip_tags);
					tags = String(data.result.tip_tags).trim().split(",");

					for (i = 0; i < tags.length; i++) {
						$('#allowSpacesTags').tagit('createTag', tags[i]);
					}
				}

				if (data.result.tip_thumbnail_image_url != undefined) {
					$('#cover_img').attr("src", data.result.tip_thumbnail_image_url);
				}

				if (data.result.tip_notes != undefined) {
					$('#info_content').html(data.result.tip_notes);
				}

				if (data.result.products != undefined) {
					$.each(data.result.products, function(index, product) {
						//console.log("product.id=" + product.id);
						//console.log("product.name=" + product.name);
						appendToProductsList(product.id, product.name, product.images[0]);
					});
				}

				if (data.result.tip_pages != undefined) {
					$.each(data.result.tip_pages, function(index, page) {
						if (page.tip_text != undefined) { //text page
							addTextPage(page.tip_text);
						} else if (page.tip_image_url != undefined) {
							//Add one image page
							addImagePage(page.tip_image_url, page.tip_image_caption);
						} else if (page.tip_video_url != undefined) {
							//Add one vedio page
							addVideoPage(page.tip_video_url);
						}
					});
				}
			}
		});
	}

	$('#submit_idea').on('click', function(e) {
		//check if the cover image and caption is not null.
		console.log("cover image src=" + $('#cover_img').attr("src"));
		console.log("cover caption =" + $('#cover_caption').html());


		if ($('#cover_img').attr("src") == undefined || $('#cover_img').attr("src").trim().length <= 0 ||
			$('#cover_caption').html().trim().length <= 0) {
			BootstrapDialog.alert('You have to set the cover image and caption before submit.');
		} else {
			submit_idea();
		}
	});

	function submit_idea() {
		var formUrl = server + idea_post + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(formUrl);
		//console.log('token=' + getCookie('token'));
		//console.log($('#allowSpacesTagsResult').val());

		var product_ids = [];
		$.each($('#editableProductList').find('li'), function(li) {
			//console.log($(this).data("product_id"));    
			product_ids.push($(this).data("product_id"))
		});


		var pages = [];
		var local_files = [];
		var filenames = [];

		$('#multi2').find('.panel-body').each(function(index, body) {
			var page = {};


			//console.log("found the panel: " + $(body).html());
			obj = $(body).children()[0];
			//console.log("embedTag is div= " + $(embedTag).is("div"));
			//console.log("embedTag is img= " + $(embedTag).is("img"));
			//console.log("embedTag is embed= " + $(embedTag).is("embed"));

			if ($(obj).is("div")) {
				console.log("It is a text page");
				page["tip_text"] = $(obj).html();
				pages.push(page);
			} else if ($(obj).is("embed")) {
				console.log("It is a video page");
				page["tip_video_url"] = $(obj).attr("src");
				pages.push(page);
			} else if ($(obj).is("img")) {
				console.log("It is a image page");
				var src = $(obj).attr("src");
				console.log("src=" + src);
				if (src.indexOf("data:image") == 0 || src.indexOf("blob:http") == 0) {
					page["tip_image_local"] = true;
					console.log("image page has local file: " + $(obj).data("file"));
					local_files.push($(obj).data("file"));
					//console.log("image page has local file name: " + $(obj).data("filename"));
					//filenames.push($(obj).data("filename"));
				} else {
					page["tip_image_url"] = src;
				}

				page["tip_image_caption"] = $(body).children("pre").html();
				pages.push(page);
			}
		});


		var message = {
			"category": $('#category option:selected').text(),
//			"tip_title": $('#cover_caption').html().trim(),
//			"tip_thumbnail_image_url": $('#cover_img').attr("src"),
			"tip_pages": pages,
			"tip_tags": $('#allowSpacesTagsResult').val().split(','),
			"products_id": product_ids
//			"tip_notes": $('#info_content').html().trim()
		}

		//console.log(message);
		var messageStr = JSON.stringify(message);
		//console.log(messageStr);

		var formData = new FormData();
		formData.append("message", messageStr);
		for (var i = 0; i < local_files.length; i++) {
			formData.append("tip_image_local_" + (i + 1), local_files[i]);
		}


		//used by ajax form submit. It is not used for the moment.
		//		for (var i = 0; i < filenames.length; i++) {
		//			$("#submit_files").append('<input type="file" name="tip_image_local_' +
		//				(i + 1) + '" value="' + filenames[i] + '">');
		//		}
		//		$("#submit_files").append('<input name="message" value="' +  messageStr +'">');
		//		console.log($("#submit_files").html());



		$.ajax({
			url: formUrl,
			type: "POST",
			//data: 'message:' + message,
			data: formData,
			processData: false,
			contentType: false,
			crossDomain: true,
			success: function(data, textStatus, jqXHR) {
				//data: return data from server
				console.log(data);
				if (data.status == 0) {
					console.log('read result from server: ' + data.result);
					BootstrapDialog.alert('The idea is posted.');
				} else {
					BootstrapDialog.alert('There is some error during submit the idea, error message =' +
						data.message);
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus);
				console.log(jqXHR);
				BootstrapDialog.alert('There is some error during submit the idea, error=' + textStatus);
			}
		});
	}


	function onSubmitSuccess(data, statusText, jqXHR) {
		console.log(data);
		if (data.status == 0) {
			console.log('read result from server: ' + data.result);
			BootstrapDialog.alert('The idea is posted.');
		} else {
			if (data.message != undefined) {
				BootstrapDialog.alert('There is some error during submit the idea, error message =' +
					data.message);
			} else {
				BootstrapDialog.alert('There is some error during submit the idea');
			}
		}
	}

	function onError(jqXHR, textStatus, errorThrown) {
		console.log(textStatus);
		console.log(jqXHR);
		BootstrapDialog.alert('There is some error during submit the idea, error=' + textStatus);
	}


	var editableList = new Sortable(multi2, {
		draggable: '.panel',
		handle: '.tile__name',
		filter: '.js-remove',
		onFilter: function(evt) {
			var el = editableList.closest(evt.item);
			el && el.parentNode.removeChild(el);
		}
	});


	$("#richeditor").ckeditor();

	//	$('.richeditor').on('click', function(e) {
	//		showRichEditorDialog($(this));
	//	});

	var holder;
	$(document).on('click', '.richeditor', function() {
		holder = $(this);
		CKEDITOR.instances.richeditor.setData(holder.html());
		$("#richeditorDialog").modal("show");
	});


	//===========================================
	//  Show rich editor dialog. 
	//  The first mehod uses BootstrapDialog, 
	//    but it could not handle editor key events.
	//===========================================

	//	function showRichEditorDialog(holder) {
	//		BootstrapDialog.show({
	//			title: 'Edit the content',
	//			closable: false,
	//			modal: false,
	//			size: BootstrapDialog.SIZE_WIDE,
	//			draggable: true,
	//			message: $('<div></div>').load('editor.html'),
	//			buttons: [{
	//				label: 'OK',
	//				action: function(dialog) {
	//					console.log($('#input2').val());
	//					holder.html($('#input2').val());
	//					dialog.close();
	//				}
	//			}, {
	//				label: 'Cancel',
	//				action: function(dialog) {
	//					dialog.close();
	//				}
	//			}],
	//			onshown: function(dialogRef) {
	//				CKEDITOR.instances.input2.setData(holder.html());
	//			},
	//		});
	//	}


	$('#save_content').click(function() {
		//console.log("save button is clicked");
		//console.log(holder.html());
		holder.html($('#richeditor').val());

		$("#richeditorDialog").modal('hide');
	});

	//	$('.panel-body > .btn-block').on('click', function(e) {
	//		showUrlInputDialog($(this));
	//	});

	$(document).on('click', '.panel-body > .btn-block', function() {
		showUrlInputDialog($(this));
	});

	function showUrlInputDialog(button) {
		BootstrapDialog.show({
			title: 'Input URL',
			//cssClass: "modal-dialog",
			message: '<form class="form-horizontal"> ' +
				'<input id="url" name="url" type="text" placeholder="input url here" class="form-control input-md"> ' +
				'</form>',
			buttons: [{
				label: 'Save',
				cssClass: 'btn-primary',
				action: function(dialogItself) {
					var url = $('#url').val();

					//url = url.replace("watch?v=", "v/");
					url = parsingVideoUrl(url);
					console.log("url=" + url);

					//console.log(button.parents().html());
					//button.prev().prev().prev().prev().attr("data", url);
					var objectTag = button.parent().children("object");
					console.log("objectTag=" + objectTag);
					if (objectTag != undefined) {
						objectTag.attr("data", url);
					}

					var embedTag = button.parent().children("embed");
					//console.log("objectTag=" + objectTag);
					if (embedTag != undefined) {
						embedTag.attr("src", url);
					}

					var ifrmaeTag = button.parent().children("iframe");
					if (objectTag != undefined) {
						objectTag.attr("src", url);
					}

					var imgTag = button.parent().children("img");
					if (imgTag != undefined) {
						imgTag.attr("src", url);
					}

					dialogItself.close();
				}
			}, {
				label: 'Cancel',
				action: function(dialogItself) {
					dialogItself.close();
				}
			}]
		});
	}

	$('#search').click(function() {
		doSearch();
	});

	$('#input-search').keypress(function(e) {
		var code = (e.keyCode ? e.keyCode : e.which);
		if (code == 13) {
			e.preventDefault();
			//$(this).closest('form').submit();
			doSearch();
		}
	});


	function doSearch() {
		var key = $('#input-search').val();
		if (key.length > 0) {

			var search_producturl = server + product_search + "&query=" + key +
				'&api_key=' + api_key + '&auth_token=' + getCookie('token');
			console.log(search_producturl);

			$.getJSON(search_producturl, function(data) {
				console.log(JSON.stringify(data));
				if (data.status == 0 && data.result.products != undefined) { //read prodcut detail successfully.

					$('#divselktr').empty();
					$.each(data.result.products, function(index, product) {
						console.log('product name=' + product.product_name);
						appendProductToDropDownList(product.product_id, product.product_name,
							product.product_image);
					});

					//console.log($('#divselktr').html());
				}
				initializeMultipleSelector();

				$("#myModal").modal('show');

			});
		}
	}

	function appendProductToDropDownList(product_id, product_name, product_image) {
		var optionObj = $('<option image="' + product_image +
			'" class="multSelktrImg">' + product_name + '</option>');
		optionObj.data("product_id", product_id);
		optionObj.data("product_image", product_image);
		
		$('#divselktr').append(optionObj);
	}


	$('#select_products').click(function() {
		console.log($('#divselktr option:selected').text());

		//Read the selected options.
		$('#divselktr option:selected').map(function(index, value) {
			var productName = $(this).html();
			//console.log("index=" + index + "   value=" + $(this).val());
			console.log("index=" + index + "   value=" + $(this).html());
			console.log("           product_id=" + $(this).data("product_id"));
			console.log("           product_image=" + $(this).data("product_image"));

			appendToProductsList($(this).data("product_id"), $(this).html(), $(this).data("product_image"));
			//update the select products.
		});
		$("#myModal").modal('hide');
	});

	function appendToProductsList(product_id, product_name, product_image) {
		var productLi = $('<li><img src="' + product_image +
			'" />' + product_name + '<i class="js-remove">✖</i></li>');
		productLi.data("product_id", product_id);
		$('#editableProductList').append(productLi);
	}

	function initializeMultipleSelector() {
		$('#divselktr').multiselect({
			multiple: true,
			height: '305px',
			header: 'See the images in the rows below:',
			noneSelectedText: 'DROPDOWN selector ...  ',
			selectedText: function(numChecked, numTotal, checkedItems) {
				return numChecked + ' of ' + numTotal + ' checked';
			},
			selectedList: false,
			show: ['blind', 200],
			hide: ['fade', 200],
			position: {
				my: 'left top',
				at: 'left bottom'
			}
		});
	}


	//-------------------------------
	// Allow spaces without quotes.
	//-------------------------------
	var allowSpacesTags = $('#allowSpacesTags').tagit({
		availableTags: [],
		allowSpaces: true,
		singleField: true,
		singleFieldNode: $('#allowSpacesTagsResult')
	});


	$('#add_video_page').click(function() {
		console.log("children size=" + $('#multi2').children().length);
		//max 10 content pages.		
		if ($('#multi2').children().length < 10) {

			$('#multi2').append('<div class="panel panel-primary tile" style="height: 400px;"><div class="tile__name" id="editable">' +
				'<div>Video page <i class="js-remove">✖</i></div></div>' +
				'<div class="panel-body">' +
				'<object width="360" height="240" border="1px"></object><br/><br/>  ' +
				//'<iframe width="360" height="240"></iframe><br/><br/>  ' +
				'<button type="button" id="change" class="btn btn-primary btn-default btn-block">Change</button>' +
				'</div></div>');
			$("html, body").animate({
				scrollTop: $(document).height()
			}, "slow");
		} else {
			BootstrapDialog.alert('You can only add 10 pages.');
		}
	});

	$('#add_Image_page').click(function() {
		//max 10 content pages.		
		if ($('#multi2').children().length < 10) {
			addImagePage("", "<p>Click to change caption.</p>");
			$("html, body").animate({
				scrollTop: $(document).height()
			}, "slow");
		} else {
			BootstrapDialog.alert('You can only add 10 pages.');
		}
	});

	$('#add_text_page').click(function() {
		//max 10 content pages.		
		if ($('#multi2').children().length < 10) {
			addTextPage('<p>Click to edit idea information page such as adding contact...</p>');
			$("html, body").animate({
				scrollTop: $(document).height()
			}, "slow");
		} else {
			BootstrapDialog.alert('You can only add 10 pages.');
		}
	});

	function parsingVideoUrl(url) {
		if (url.indexOf("youtube.com") >= 0) {
			url = url.replace("watch?v=", "v/");
		} else if (url.indexOf("youtu.be") >= 0) {
			url = url.replace("youtu.be", "www.youtube.com/v");
		}

		return url;
	}

	function addVideoPage(url) {
		//console.log("get url=" + url);
		url = parsingVideoUrl(url);
		//console.log("url after parsing =" + url);


		$('#multi2').append('<div class="panel panel-primary tile" style="height: 400px;"><div class="tile__name" id="editable">' +
			'<div>Video page <i class="js-remove">✖</i></div></div>' +
			'<div class="panel-body">' +
			'<object width="360" height="240" border="1px" class="embed-style" data="' + url + '"></object><br/><br/>  ' +
			//'<iframe width="360" height="240"></iframe><br/><br/>  ' +
			'<button type="button" id="change" class="btn btn-primary btn-default btn-block">Change</button>' +
			'</div></div>');
	}


	function addImagePage(url, text) {
		$('#multi2').append('<div class="panel panel-primary tile" style="height: 400px;"><div class="tile__name" id="editable">' +
			'<div>Image page <i class="js-remove">✖</i></div></div>' +
			'<div class="panel-body">' +
			'<img class="image_page_img" src="' + url + '"/><br/>' +
			'<pre class="richeditor">' + text + '</pre>' +
			'<button type="button" id="change" class="btn btn-primary btn-default btn-block">Add Url</button><br/><input type="file" onchange="fileUploadOnChange(this)">' +
			'</div></div>');
	}

	function addTextPage(text) {
		$('#multi2').append('<div class="panel panel-primary tile" style="height: 400px;"><div class="tile__name" id="editable">' +
			'<div>Text page <i class="js-remove">✖</i></div></div>' +
			'<div class="panel-body">' +
			'<div class="richeditor" style="height: 310px;">' + text + '</div>' +
			'</div></div>');
	}



	var container = document.getElementById("editableProductList");
	var editableProductsList = new Sortable(container, {
		animation: 150,
		filter: '.js-remove',
		onFilter: function(evt) {
			var el = editableProductsList.closest(evt.item);
			el && el.parentNode.removeChild(el);
		}
	});
})

function fileUploadOnChange(upload) {
	//console.log("Upload is changed: " + upload.files[0]);	
	//console.log("upload is changed: " + $(upload).val());

	var img = $(upload).siblings('.image_page_img');
	if (img != undefined) {
		readURL(upload, img);
		console.log("the stored image file: " + img.data("file"));
	}
}

function readURL(input, image) {
	if (input.files && input.files[0]) {
		//console.log(input.files[0]);

		var url = window.URL.createObjectURL(input.files[0]);
		image.attr('src', url);

		image.data("file", input.files[0]);
		image.data("filename", $(input).val());
	}
}