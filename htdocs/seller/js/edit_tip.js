// Sets the min-height of #page-wrapper to window size
$(function() {

	//-------------------------------
	// Allow spaces without quotes.
	//-------------------------------
	var allowSpacesTags = $('#allowSpacesTags').tagit({
		availableTags: [],
		allowSpaces: true,
		singleField: true,
		singleFieldNode: $('#allowSpacesTagsResult')
	});


	idea_id = getUrlParameter("tip_id");
	tip_thumbnail_image_url = "";

	//$("#allowSpacesTags").tagit();

	console.log("idea_id=" + idea_id);
	loadIdea();



	function loadIdea() {
		if (idea_id != undefined && idea_id != null) { //edit data for teh prodcut. get the prodcut detail first of all.
			$('#page_title').text("Edit Idea");
			$('#idea_id').val(idea_id);


			//Check if there is a draft version saved in local.
			var draftIdeaStr = localStorage.getItem("idea");

			if (draftIdeaStr != undefined) {

				var savedIdea = JSON.parse(draftIdeaStr);

				//We only load the draft version when the id is the same.
				if (savedIdea.idea_id == idea_id) {
					loadDraftIdea(savedIdea);
				} else {
					getIdeaDetail();
				}
			} else {
				getIdeaDetail();
			}
		} else {
			//otehrwise, create a new prodcut.
			loadDraftIdea();
		}
	}

	function loadDraftIdea(draftIdea) {
		console.log("loadDraftIdea=" + JSON.stringify(draftIdea));

		if (draftIdea == undefined) {
			var draftIdeaStr = localStorage.getItem("idea");
			console.log("loaded draft idea: " + draftIdeaStr);
			
			if (draftIdeaStr != undefined) {

				draftIdea = JSON.parse(draftIdeaStr);
			}
		}

		if (draftIdea != undefined) {
			updateIdeaUI(draftIdea.tip_title, draftIdea.category, draftIdea.tip_tags,
				draftIdea.tip_thumbnail_image_url, draftIdea.tip_notes, draftIdea.products,
				draftIdea.tip_pages);
		}
	}


	//check if it is IE.
	function msieversion() {

		var ua = window.navigator.userAgent;
		var msie = ua.indexOf("MSIE ");

		if (msie > 0 || !!navigator.userAgent.match(/Trident.*rv\:11\./)) {
			// If Internet Explorer, return version number
			return true;
		}

		// If another browser, return 0
		return false;
	}

	function getIdeaDetail() {
		var get_ideaurl = server + idea_get + "&tip_id=" + idea_id +
			'&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(get_ideaurl);

		$.getJSON(get_ideaurl, function(data) {
			console.log(JSON.stringify(data));
			if (data.status == 0) { //read prodcut detail successfully.

				updateIdeaUI(data.result.tip_title, data.result.tip_category, data.result.tip_tags,
					data.result.tip_thumbnail_image_url, data.result.tip_notes, data.result.products,
					data.result.tip_pages);
			} else if (data.status == -20) {
				window.location.href = "login.html";
			}
		});
	}

	function updateIdeaUI(tip_title, tip_category, tip_tags, thumbnail_image_url, tip_notes, products, tip_pages) {
		$('#title').val(tip_title);
		$('#category').val(tip_category);

		if (tip_tags != undefined && tip_tags.length > 0) {
			//console.log("tags original = " + data.result.tip_tags);
			tags = String(tip_tags).trim().split(",");

			for (i = 0; i < tags.length; i++) {
				$('#allowSpacesTags').tagit('createTag', tags[i]);
			}
		}

		if (thumbnail_image_url != undefined) {
			$('#cover_img').attr("src", thumbnail_image_url);
			tip_thumbnail_image_url = thumbnail_image_url;
		}

		if (tip_notes != undefined) {
			$('#info_content').html(tip_notes);
		}

		if (products != undefined) {
			$.each(products, function(index, product) {
				//console.log("product.id=" + product.id);
				//console.log("product.name=" + product.name);
				appendToProductsList(product.id, product.name, product.images[0]);
			});
		}

		if (tip_pages != undefined) {
			$.each(tip_pages, function(index, page) {
				if (page.tip_text != undefined) { //text page
					addTextPage(page.tip_text);
				} else if (page.tip_image_url != undefined) {
					//Add one image page
					addImagePage(page.tip_image_url, page.tip_image_caption);
				} else if (page.tip_image_local_url != undefined) {
					//Add one image page
					addImagePage("", page.tip_image_caption);


					console.log($('#multi2').last().html());
					
					$('#multi2').last().find("input").value = page.tip_thumbnail_image_url;
					$('#multi2').last().find("input").val(page.tip_thumbnail_image_url);
					console.log($('#multi2').last().html());

					
				}else if (page.tip_video_url != undefined) {
					//Add one vedio page
					addVideoPage(page.tip_video_url);
				}
			});
		}
	}

	$('#submit_idea').on('click', function(e) {
		//check if the cover image and caption is not null.
		//console.log("cover image src=" + $('#cover_img').attr("src"));
		//console.log("cover caption =" + $('#cover_caption').html());


		//cover page is removed. Remove the error check. Submit the idea directly.
		//		if ($('#cover_img').attr("src") == undefined || $('#cover_img').attr("src").trim().length <= 0 ||
		//			$('#cover_caption').html().trim().length <= 0) {
		//			BootstrapDialog.alert('You have to set the cover image and caption before submit.');
		//		} else {
		//			submit_idea();
		//		}
		submit_idea();
	});

	function submit_idea() {
		var formUrl = server + idea_post + '&api_key=' + api_key + '&auth_token=' + getCookie('token');
		console.log(formUrl);

		var local_files = [];
		var message = getFormData(local_files);

		var messageStr = JSON.stringify(message);

		var formData = new FormData();
		formData.append("message", messageStr);
		formData.append("idea_id", idea_id);
		for (var i = 0; i < local_files.length; i++) {
			formData.append("tip_image_local_" + (local_files[i].id + 1), local_files[i].file);
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
				console.log('read result from server: ' + JSON.stringify(data));
				if (data.status == -20) {
					BootstrapDialog.alert('You have signed out. Please sign in first.');
				} else if (data.status == 0) {
					idea_id = data.result.idea_id;
					$('#idea_id').val(idea_id);

					//When submit successfully, remove the local draft version.
					localStorage.removeItem("idea");

					BootstrapDialog.alert('The idea is posted.');
				} else {
					if (data.message != undefined) {
						BootstrapDialog.alert('There is some error during submit the idea. \n\n' +
							data.message);
					} else {
						BootstrapDialog.alert('There is some error during submit the idea');
					}
				}
			},
			error: function(jqXHR, textStatus, errorThrown) {
				console.log(textStatus);
				console.log(jqXHR);
				BootstrapDialog.alert('There is some error during submit the idea. \n\n' + textStatus);
			}
		});
	}


	//==========================================================================
	// Retreive data from Form. 
	//==========================================================================
	function getFormData(local_files) {
		if (local_files == undefined) {
			local_files = [];
		}

		var product_ids = [];
		$.each($('#editableProductList').find('li'), function(li) {
			//console.log($(this).data("product_id"));    
			product_ids.push($(this).data("product_id"))
		});




		var pages = [];

		var filenames = [];
		var tip_image_local_cover = false;

		getPages(pages, local_files, tip_image_local_cover);




		var message = {
			"idea_id": idea_id,
			"category": $('#category option:selected').text(),
			"tip_title": $('#title').val(),
			"tip_thumbnail_image_url": tip_thumbnail_image_url,
			"tip_pages": pages,
			"tip_image_local_cover": tip_image_local_cover,
			"tip_tags": $('#allowSpacesTagsResult').val().split(','),
			"products_id": product_ids
				//"tip_notes": $('#info_content').html().trim()
		}

		return message;
	}

	function getPages(pages, local_files, tip_image_local_cover) {
		//The flag to show if the thumbnail image url is updated.
		var update_thumbnail_image_url = false;

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
			} else if ($(obj).is("iframe")) {
				console.log("It is a video iframe page");
				page["tip_video_url"] = $(obj).attr("src");
				pages.push(page);

				if (!update_thumbnail_image_url) {
					update_thumbnail_image_url = true;
					tip_thumbnail_image_url = getVideoThumbanil($(obj).attr("src"));
				}

			} else if ($(obj).is("embed")) {
				console.log("It is a video embed page");
				page["tip_video_url"] = $(obj).attr("src");
				pages.push(page);

				if (!update_thumbnail_image_url) {
					update_thumbnail_image_url = true;
					tip_thumbnail_image_url = getVideoThumbanil($(obj).attr("src"));
				}

			} else if ($(obj).is("object")) {
				console.log("It is a video object page");

				var video_url = $(obj).attr("data");
				console.log("src=" + video_url);
				page["tip_video_url"] = video_url;
				pages.push(page);


				if (!update_thumbnail_image_url) {
					update_thumbnail_image_url = true;
					tip_thumbnail_image_url = getVideoThumbanil(video_url);
				}
			} else if ($(obj).is("img")) {
				console.log("It is a image page");
				
				var filepath = $(obj).data("filename");
				console.log("filename: " + filepath);
				
				var src = $(obj).attr("src");
				console.log("src=" + src);
				if (src.indexOf("data:image") == 0 || src.indexOf("blob:http") == 0 || 
						(msieversion() && src.indexOf("blob:") == 0)) {

					//Local image.
					page["tip_image_local"] = true;
					page["tip_image_local_url"] = filepath;
					console.log("image page has local file: " + $(obj).data("file"));
					var file_obj = {
						id: index,
						file: $(obj).data("file"),
						url: src
					}
					local_files.push(file_obj);


					if (!update_thumbnail_image_url) {
						update_thumbnail_image_url = true;
						tip_image_local_cover = true;
						tip_thumbnail_image_url = undefined;
					}
				} else {

					//Image url
					page["tip_image_url"] = src;

					//Update thumbnail url.
					if (!update_thumbnail_image_url) {
						update_thumbnail_image_url = true;
						tip_thumbnail_image_url = src;
					}
				}

				page["tip_image_caption"] = $(body).children("pre").html();
				pages.push(page);
			}
		});
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

	$(document).on('click', '.panel-body > .row > .col-md-4 > .btn-xs', function() {
		showUrlInputDialog($(this), $(this).parent().parent().parent().find(".image_page_img"));
		//		$(".panel-body > .image_page_img"));
	});

	//For video iput dialog
	$(document).on('click', '.panel-body > .btn-primary', function() {
		showUrlInputDialog($(this), null);
	});

	function showUrlInputDialog(button, imgContainer) {
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


					if (imgContainer != null) {
						console.log("this is a image page");

						IsValidImageUrl(url, function(url, isValidImageUrl) {
							if (isValidImageUrl) {
								$(imgContainer).attr("src", url);
							} else {
								BootstrapDialog.alert('Url is not a valid image. \n\n' + url);
							}
						})


						//Clear file upload compoment.
						$(imgContainer).parent().find(".row > .col-md-6 > .btn-default").val("");
					} else {
						console.log("this is a video page");

						//url = url.replace("watch?v=", "v/");
						url = parsingVideoUrl(url);
						console.log("url=" + url);

						$(button).prevAll().each(function() {
							//alert($(this).prop('outerHTML'));
							//console.log("is object=" + $(this).is("object"));

							if ($(this).is("object")) {
								console.log("It is object tag.");
								$(this).attr("data", url);
							}

							if ($(this).is("embed")) {
								console.log("It is embed tag.");
								$(this).attr("src", url);
								console.log($(this).prop('outerHTML'));
							}

							if ($(this).is("iframe")) {
								console.log("It is iframe tag.");
								$(this).attr("src", url);
							}

							if ($(this).is("img")) {
								console.log("It is img tag.");
								$(this).attr("src", url);
							}
						});
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

			var search_producturl = server + product_search + "&offset=0&limit=200&query=" + key +
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
			'" class="multSelktrImg">' + product_name + ' (product id ' + product_id + ')</option>');
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
		productLi.data("product_name", product_name);
		productLi.data("product_image", product_image);
		$('#editableProductList').append(productLi);
	}

	function initializeMultipleSelector() {
		$('#divselktr').multiselect({
			multiple: true,
			height: '305px',
			header: 'Up to 10 products',
			noneSelectedText: 'Link products into idea',
			selectedText: function(numChecked, numTotal, checkedItems) {
				return numChecked + ' of ' + numTotal + ' checked';
			},
			selectedList: false,
			show: ['blind', 200],
			hide: ['fade', 200],
			position: {
				my: 'left top',
				at: 'left bottom'
			},
			click: function(e) {
				if ($(this).multiselect("widget").find("input:checked").length > 10) {
					$(".message").addClass("alert-danger").removeClass("alert-info").html("You can only check up to 10 prodcuts");
					return false;
				} else {
					$(".message").addClass("alert-info").removeClass("alert-danger").html("Select products below.");
				}
			}
		});
	}





	$('#add_video_page').click(function() {
		console.log("children size=" + $('#multi2').children().length);
		//max 10 content pages.		
		if ($('#multi2').children().length < 10) {


			if (msieversion()) {
				console.log("I am IE.");

				//				$('#multi2').append('<div class="panel panel-primary tile" style="height: 400px;"><div class="tile__name" id="editable">' +
				//					'<div>Video page <i class="js-remove">✖</i></div></div>' +
				//					'<div class="panel-body">' +
				//					'<embed width="360" height="240" border="1px" src="" type="application/x-shockwave-flash" /><br/><br/>  ' +
				//					//'<iframe width="360" height="240"></iframe><br/><br/>  ' +
				//					'<button type="button" id="change" class="btn btn-primary btn-default btn-block">Change</button>' +
				//					'</div></div>');

				$('#multi2').append('<div class="panel panel-primary tile" style="height: 400px;"><div class="tile__name" id="editable">' +
					'<div>Video page <i class="js-remove">✖</i></div></div>' +
					'<div class="panel-body">' +
					'<iframe width="360" height="240" border="1px" src="" /><br/><br/>  ' +
					//'<iframe width="360" height="240"></iframe><br/><br/>  ' +
					'<button type="button" id="change" class="btn btn-primary btn-default btn-block">Change</button>' +
					'</div></div>');
			} else {
				console.log("I am NOT IE.");

				$('#multi2').append('<div class="panel panel-primary tile" style="height: 400px;"><div class="tile__name" id="editable">' +
					'<div>Video page <i class="js-remove">✖</i></div></div>' +
					'<div class="panel-body">' +
					'<object width="360" height="240" border="1px" data=""></object><br/><br/>  ' +
					//'<iframe width="360" height="240"></iframe><br/><br/>  ' +
					'<button type="button" id="change" class="btn btn-primary btn-default btn-block">Change</button>' +
					'</div></div>');

			}


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
			addImagePage("", "");
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
		//Remove the list and index parameters if url contains.
		url = removeParam("index", url);
		url = removeParam("list", url);

		if (url.indexOf("youtube.com") >= 0) {
			//url = url.replace("watch?v=", "v/");
			url = url.replace("watch?v=", "embed/");
		} else if (url.indexOf("youtu.be") >= 0) {
			//url = url.replace("youtu.be", "www.youtube.com/v");
			url = url.replace("youtu.be", "www.youtube.com/embed");
		}

		if (url.indexOf("?") >= 0) {
			url = url + "&wmode=opaque";
		} else {
			url = url + "?wmode=opaque";
		}

		return url;
	}


	//Remove one parameter from url.
	//var originalURL = "http://yourewebsite.com?id=10&color_id=1";
	//var alteredURL = removeParam("color_id", originalURL);
	function removeParam(key, sourceURL) {
		var rtn = sourceURL.split("?")[0],
			param,
			params_arr = [],
			queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
		if (queryString !== "") {
			params_arr = queryString.split("&");
			for (var i = params_arr.length - 1; i >= 0; i -= 1) {
				param = params_arr[i].split("=")[0];
				if (param === key) {
					params_arr.splice(i, 1);
				}
			}
			rtn = rtn + "?" + params_arr.join("&");
		}
		return rtn;
	}


	function getVideoThumbanil(url) {
		var start = url.lastIndexOf("/");
		var video_id = url.substring(start);
		//console.log("video_id = " + video_id);

		var thumbnail_url = "http://img.youtube.com/vi" + video_id + "/0.jpg";
		thumbnail_url = thumbnail_url.replace("?wmode=opaque", "");
		//console.log("video thumbnail url = " + thumbnail_url);

		return thumbnail_url;
	}



	function addVideoPage(url) {
		//console.log("get url=" + url);
		url = parsingVideoUrl(url);
		//console.log("url after parsing =" + url);


		if (msieversion()) {
			console.log("I am IE.");
			//			$('#multi2').append('<div class="panel panel-primary tile" style="height: 400px;"><div class="tile__name" id="editable">' +
			//				'<div>Video page <i class="js-remove">✖</i></div></div>' +
			//				'<div class="panel-body">' +
			//				'<embed width="360" height="240" border="1" class="embed-style" src="' + url + '" type="application/x-shockwave-flash" /><br/><br/>  ' +
			//				'<button type="button" id="change" class="btn btn-primary btn-default btn-block">Change</button>' +
			//				'</div></div>');

			$('#multi2').append('<div class="panel panel-primary tile" style="height: 400px;"><div class="tile__name" id="editable">' +
				'<div>Video page <i class="js-remove">✖</i></div></div>' +
				'<div class="panel-body">' +
				'<iframe width="360" height="240" border="1" class="embed-style" src="' + url + ' /><br/><br/>  ' +
				'<button type="button" id="change" class="btn btn-primary btn-default btn-block">Change</button>' +
				'</div></div>');

		} else {
			console.log("I am NOT IE.");
			$('#multi2').append('<div class="panel panel-primary tile" style="height: 400px;"><div class="tile__name" id="editable">' +
				'<div>Video page <i class="js-remove">✖</i></div></div>' +
				'<div class="panel-body">' +
				'<object width="360" height="240" border="1" class="embed-style" data="' + url + '"></object><br/><br/>  ' +
				'<button type="button" id="change" class="btn btn-primary btn-default btn-block">Change</button>' +
				'</div></div>');
		}
	}


	function addImagePage(url, text) {
		console.log("addImagePage, url=" + url);
		
		if (text == undefined) {
			text = "";
		}

		$('#multi2').append('<div class="panel panel-primary tile" style="height: 400px;"><div class="tile__name" id="editable">' +
			'<div>Image page <i class="js-remove">✖</i></div></div>' +
			'<div class="panel-body">' +
			'<img class="image_page_img" src="' + url + '"/>' +
			' <br/><div class="row"> <div class="col-md-4">' +
			'<button type="button" id="change" class="btn btn-primary btn-xs">Input Image Url</button></div>' +
			'<div class="col-md-6"><input type="file" onchange="fileUploadOnChange(this)" class="btn-default">' +
			'</div></div>' +
			'<br/>click below to change image caption' +
			'<br/><pre class="richeditor" style="height:40px">' + text + '</pre>' +


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


	//Called when preview dialog is shown.
	$(document).on('show.bs.modal', '#previewIdea', function() {
		//console.log("preview modal is shown with idea id = " + idea_id);
		var modal = $(this);

		modal.find('.modal-title').text($("#title").val());


		if (idea_id != undefined) {
			$("#preview_idea_id").html("ID: " + idea_id);
		}

		$("#preview_idea_category").html("Category: " + $("#category :selected").text());
		$("#preview_idea_tags").html("Tags: " + $("#allowSpacesTagsResult").val());

		$("#preview_idea_content").empty();
		//modal.find('.modal-body input').val(recipient)

		var pages = [];
		var local_files = [];
		var tip_image_local_cover = false;


		//Load all the pages and display.
		getPages(pages, local_files, tip_image_local_cover);
		for (var i = 0; i < pages.length; i++) {
			page = pages[i];

			if (page.tip_text != undefined) {
				$("#preview_idea_content").append('<br />' + page.tip_text);
			} else if (page.tip_video_url != undefined) {
				if (msieversion()) {
					$("#preview_idea_content").append('<br /><br /><iframe width="360" height="240" border="1" class="embed-style" src="' + page.tip_video_url + ' />');
				} else {
					$("#preview_idea_content").append('<br /><br /><object width="360" height="240" border="1" class="embed-style" data="' + page.tip_video_url + '"></object>');
				}

			} else if (page.tip_image_url != undefined) {
				$("#preview_idea_content").append('<br /><img class="image_page_img" src="' + page.tip_image_url + '"/>');

				//Add image caption below as well.
				if (page.tip_image_caption != undefined) {
					$("#preview_idea_content").append('<br />' + page.tip_image_caption);
				}

			} else if (page.tip_image_local != undefined && page.tip_image_local == true) {
				for (var j = 0; j < local_files.length; j++) {
					if (local_files[j].id == i) {
						$("#preview_idea_content").append('<br /><img class="image_page_img" src="' + local_files[j].url + '"/>');
						break;
					}
				}
				//Add image caption below as well.
				if (page.tip_image_caption != undefined) {
					$("#preview_idea_content").append('<br />' + page.tip_image_caption);
				}
			}
		}


		//Load products. data.result.products
		$("#preview_idea_editableProductList").empty();

		var lis = $("#editableProductList li");
		console.log("length=" + lis.length);

		if (lis != undefined && lis.length > 0) {
			$("#editableProductList").each(function(index) {
				var txt = $(this).html();
				txt = txt.replace(/\✖/g, "");
				$("#preview_idea_editableProductList").append(txt);
			});
		} else {
			$("#preview_idea_products_panel").hide();
		}

	});


	$('#save_idea').click(function() {
		var local_files = [];
		var message = getFormData();
		message.products = readProducts();

		var messageStr = JSON.stringify(message);

		window.localStorage.setItem("idea", messageStr);

		console.log("Idea is saved as: " + localStorage.getItem("idea"));
	});

	function readProducts() {
		var products = [];
		$.each($('#editableProductList').find('li'), function(li) {
			//console.log($(this).data("product_id"));

			var images = [];
			images.push($(this).data("product_image"));

			var product = {
				"id": $(this).data("product_id"),
				"name": $(this).data("product_name"),
				"images": images
			}
			products.push(product);
		});

		return products;
	}
});


//=====================================================================
// 检查URL是否图像URL，callback为回调函数
//=====================================================================
function IsValidImageUrl(url, callback) {
	var img = new Image();
	img.onerror = function() {
		callback(url, false);
	}
	img.onload = function() {
		callback(url, true);
	}
	img.src = url;
}


function fileUploadOnChange(upload) {
	//console.log("Upload is changed: " + upload.files[0]);	
	//console.log("upload is changed: " + $(upload).val());

	var img = $(upload).parent().parent().siblings('.image_page_img');
	if (img != undefined) {
		readURL(upload, img);
		//console.log("the stored image file: " + img.data("file"));
	}
}

function readURL(input, image) {
	if (input.files && input.files[0]) {
		//console.log(input.files[0]);

		//var url = window.URL.createObjectURL(input.files[0]);
		var url = createObjectURL(input.files[0]);
		image.attr('src', url);

		image.data("file", input.files[0]);
		image.data("filename", $(input).val());
		//console.log("image file path = " + $(input).val());
						
				console.log("data url: " +  window.URL.createObjectURL(input.files[0]));
				//console.log("data url: " +  input.files[0].getAsDataURL());
				
				
				var fr = new FileReader;
        fr.onloadend = function(event) {
        	console.log("reader is ready:  " + event.target.result);
        	
        	image.data("image_data", event.target.result);
        	
        }
        fr.readAsDataURL(input.files[0]);
		
	}
}


function createObjectURL(file) {
	if (window.webkitURL) {
		return window.webkitURL.createObjectURL(file);
	} else if (window.URL && window.URL.createObjectURL) {
		return window.URL.createObjectURL(file);
	} else {
		return null;
	}
}