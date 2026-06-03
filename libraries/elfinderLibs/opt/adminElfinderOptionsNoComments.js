/**
 * Modified elFinder config
 *
 * @type  Object
 * @autor Dmitry (dio) Levashov
 */

elFinder.prototype._options = {
	cdns : {
		ace        : 'https://cdnjs.cloudflare.com/ajax/libs/ace/1.43.3',
		codemirror : 'https://cdnjs.cloudflare.com/ajax/libs/codemirror/6.65.7',
		ckeditor   : 'https://cdnjs.cloudflare.com/ajax/libs/ckeditor/4.22.1',
		ckeditor5  : 'https://cdn.ckeditor.com/ckeditor5/40.2.0',
		tinymce    : 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.6',
		simplemde  : 'https://cdnjs.cloudflare.com/ajax/libs/simplemde/1.11.2',
		fabric     : 'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1',
		fabric16   : 'https://cdnjs.cloudflare.com/ajax/libs/fabric.js/1.6.7',
		tui        : 'https://uicdn.toast.com',
		hls        : 'https://cdnjs.cloudflare.com/ajax/libs/hls.js/1.6.13/hls.min.js',
		dash       : 'https://cdnjs.cloudflare.com/ajax/libs/dashjs/4.7.4/dash.all.min.js',
		flv        : 'https://cdnjs.cloudflare.com/ajax/libs/flv.js/1.6.2/flv.min.js',
		videojs    : 'https://cdnjs.cloudflare.com/ajax/libs/video.js/8.23.4',
		prettify   : 'https://cdn.jsdelivr.net/gh/google/code-prettify@e006587b4a893f0281e9dc9a53001c7ed584d4e7/loader/run_prettify.js',
		psd        : 'https://cdnjs.cloudflare.com/ajax/libs/psd.js/3.4.0/psd.min.js',
		rar        : 'https://cdn.jsdelivr.net/gh/nao-pon/rar.js@6cef13ec66dd67992fc7f3ea22f132d770ebaf8b/rar.min.js',
		zlibUnzip  : 'https://cdn.jsdelivr.net/gh/imaya/zlib.js@0.3.1/bin/unzip.min.js',
		zlibGunzip : 'https://cdn.jsdelivr.net/gh/imaya/zlib.js@0.3.1/bin/gunzip.min.js',
		bzip2      : 'https://cdn.jsdelivr.net/gh/nao-pon/bzip2.js@0.8.0/bzip2.js',
		marked     : 'https://cdnjs.cloudflare.com/ajax/libs/marked/11.2.0/marked.min.js',
		sparkmd5   : 'https://cdnjs.cloudflare.com/ajax/libs/spark-md5/3.0.2/spark-md5.min.js',
		jssha      : 'https://cdnjs.cloudflare.com/ajax/libs/jsSHA/3.3.1/sha.min.js',
		amr        : 'https://cdn.jsdelivr.net/gh/yxl/opencore-amr-js@dcf3d2b5f384a1d9ded2a54e4c137a81747b222b/js/amrnb.js',
		tiff       : 'https://cdn.jsdelivr.net/gh/seikichi/tiff.js@545ede3ee46b5a5bc5f06d65954e775aa2a64017/tiff.min.js'
	},
	
	url : '',
	requestType : 'get',
	cors : null,
	parrotHeaders : [],
	requestMaxConn : 3,
	transport : {},
	urlUpload : '',
	dragUploadAllow : 'auto',
	overwriteUploadConfirm : true,
	uploadMaxChunkSize : 10485760,
	folderUploadExclude : {
		win: /^(?:desktop\.ini|thumbs\.db)$/i,
		mac: /^\.ds_store$/i
	},
	iframeTimeout : 0,
	customData : {},
	handlers : {},
	customHeaders : {},
	xhrFields : {},
	lang : 'en',
	baseUrl : '',
	i18nBaseUrl : '',
	workerBaseUrl : '',
	cssAutoLoad : true,
	themes : {},
	theme : null,
	maxErrorDialogs : 5,
	cssClass : '',
	commands : ['*', 'seeComments', 'lockFile', 'rm', 'sendToMondayChat', 'sendToThursdayChat', 'mv'],

	commandsOptions : {
		getfile : {
			onlyURL  : false,
			multiple : false,
			folders  : false,
			oncomplete : '',
			onerror : '',
			getPath    : true, 
			getImgSize : false
		},
		open : {
			method : 'post',
			into   : 'window',
			selectAction : 'open'
		},
		opennew : {
			url : '',
			useOriginQuery : true
		},
		upload : {
			ui : 'button'
		},
		download : {
			maxRequests : 100,
			minFilesZipdl : 2
		},
		quicklook : {
			autoplay : true,
			width    : 450,
			height   : 300,
			mediaControlsList : '',
			pdfToolbar : true,
			textInitialLines : 100,
			prettifyMaxLines : 300,
			contain : false,
			docked   : 0,
			dockHeight : 'auto',
			dockAutoplay : false,
			googleMapsApiKey : '',
			googleMapsOpts : {
				maps : {},
				kml : {
					suppressInfoWindows : false,
					preserveViewport : false
				}
			},
			viewerjs : {
				url: '',
				mimes: ['application/pdf', 'application/vnd.oasis.opendocument.text', 'application/vnd.oasis.opendocument.spreadsheet', 'application/vnd.oasis.opendocument.presentation'],
				pdfNative: true
			},
			sharecadMimes : [],
			googleDocsMimes : [],
			officeOnlineMimes : [],
			getDimThreshold : '200K',
			unzipMaxSize : '50M',
			mimeRegexNotEmptyCheck : /^application\/vnd\.google-apps\./
		},
		edit : {
			dialogWidth : void(0),
			dialogHeight : void(0),
			mimes : [],
			mkfileHideMimes : [],
			makeTextMimes : ['text/plain', 'text/css', 'text/html'],
			useStoredEditor : false,
			editorMaximized : false,
			editors : [
			],
			encodings : ['Big5', 'Big5-HKSCS', 'Cp437', 'Cp737', 'Cp775', 'Cp850', 'Cp852', 'Cp855', 'Cp857', 'Cp858', 
				'Cp862', 'Cp866', 'Cp874', 'EUC-CN', 'EUC-JP', 'EUC-KR', 'GB18030', 'ISO-2022-CN', 'ISO-2022-JP', 'ISO-2022-KR', 
				'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 
				'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-13', 'ISO-8859-15', 'KOI8-R', 'KOI8-U', 'Shift-JIS', 
				'Windows-1250', 'Windows-1251', 'Windows-1252', 'Windows-1253', 'Windows-1254', 'Windows-1257'],
			extraOptions : {
				uploadOpts : {},
				tuiImgEditOpts : {
					iconsPath : void(0),
					theme : {}
				},
				pixo: {
					apikey: ''
				},
				managerUrl : null,
				ckeditor: {},
				ckeditor5: {
					mode: 'decoupled-document'
				},
				tinymce : {},
				onlineConvert : {
					maxSize  : 100,
					showLink : true
				}
			}
		},
		fullscreen : {
			mode: 'screen'
		},
		search : {
			incsearch : {
				enable : true,
				minlen : 1,
				wait   : 500
			},
			searchTypes : {
				SearchMime : {
					name : 'btnMime',
					title : 'searchMime',
					incsearch : 'mime'
				}
			}
		},
		info : {
			nullUrlDirLinkSelf : true,
			hideItems : [],
			showHashMaxsize : 104857600,
			showHashAlgorisms : ['md5', 'sha256'],
			showHashOpts : {
				shake128len : 256,
				shake256len : 512
			},
			custom : {
			}
		},
		mkdir: {
			intoNewFolderToolbtn: false
		},
		resize: {
			grid8px : 'disable',
			presetSize : [[320, 240], [400, 400], [640, 480], [800,600]],
			getDimThreshold : 204800,
			dimSubImgSize : 307200
		},
		rm: {
			quickTrash : true,
			infoCheckWait : 10,
			toTrashMaxItems : 1000
		},
		paste : {
			moveConfirm : false
		},
		help : {
			view : ['about', 'shortcuts', 'help', 'integrations', 'debug'],
			helpSource : ''
		},
		preference : {
			width: 600,
			height: 400,
			categories: null,
			prefs: null,
			langs: null,
			selectActions : ['open', 'edit/download', 'resize/edit/download', 'download', 'quicklook']
		}
	},
	
	disabledCmdsRels : {
		'get'       : ['edit'],
		'rm'        : ['cut', 'empty'],
		'file&url=' : ['download', 'zipdl']
	},

	bootCallback : null,
	getFileCallback : null,
	defaultView : 'icons',
	startPathHash : '',
	sound : true,
	ui : ['toolbar', 'tree', 'path', 'stat'],
	
	uiOptions : {
		toolbar : [
			['home', 'back', 'forward', 'up', 'reload'],
			['netmount'],
			['mkdir', 'mkfile', 'upload'],
			['open', 'download', 'getfile'],
			['undo', 'redo'],
			['copy', 'cut', 'paste', 'rm', 'empty', 'hide'],
			['duplicate', 'rename', 'edit', 'resize', 'chmod'],
			['selectall', 'selectnone', 'selectinvert'],
			['quicklook', 'info'],
			['extract', 'archive'],
			['search'],
			['view', 'sort'],
			['preference', 'help'],
			['fullscreen']
		],
		toolbarExtra : {
			displayTextLabel: false,
			labelExcludeUA: ['Mobile'],
			autoHideUA: ['Mobile'],
			defaultHides: ['home', 'reload'],
			showPreferenceButton: 'none',
			preferenceInContextmenu: true
		},
		tree : {
			attrTitle : true,
			openRootOnLoad : true,
			openCwdOnOpen  : true,
			syncTree : true,
			subTreeMax : 100,
			subdirsMaxConn : 2,
			subdirsAtOnce : 5,
			durations : {
				slideUpDown : 'fast',
				autoScroll : 'fast'
			}
		},
		navbar : {
			minWidth : 150,
			maxWidth : 500,
			autoHideUA: []
		},
		navdock : {
			disabled : false,
			initMaxHeight : '50%',
			maxHeight : '90%'
		},
		cwd : {
			oldSchool : false,
			showSelectCheckboxUA : ['Touch'],
			metakeyDragout : true,
			listView : {
				columns : ['perm', 'date', 'size', 'kind'],
				columnsCustomName : {},
				fixedHeader : true
			},
			iconsView : {
				size: 0,
				sizeMax: 3,
				sizeNames: {
					0: 'viewSmall',
					1: 'viewMedium',
					2: 'viewLarge',
					3: 'viewExtraLarge' 
				}
			}
		},
		path : {
			toWorkzoneWithoutNavbar : true
		},
		dialog : {
			focusOnMouseOver : true
		},
		toast : {
			animate : {
				showMethod: 'fadeIn',
				showDuration: 300,
				showEasing: 'swing',
				timeOut: 3000,
				hideMethod: 'fadeOut',
				hideDuration: 1500,
				hideEasing: 'swing'
			}
		}
	},

	dispInlineRegex : '^(?:(?:image|video|audio)|application/(?:x-mpegURL|dash\+xml)|(?:text/plain|application/pdf)$)',
	onlyMimes : [],
	sortRules : {},
	sortType : 'name',
	sortOrder : 'asc',
	sortStickFolders : true,
	sortAlsoTreeview : false,
	clientFormatDate : true,
	UTCDate : false,
	dateFormat : '',
	fancyDateFormat : '',
	fileModeStyle : 'both',
	width : 'auto',
	height : 400,
	noResizeBySelf : false,
	heightBase : null,
	resizable : true,
	notifyDelay : 500,
	notifyDialog : {position : {}, width : null, canClose : false, hiddens : ['open']},
	dialogContained : false,
	allowShortcuts : true,
	rememberLastDir : true,
	reloadClearHistory : false,
	useBrowserHistory : true,
	showFiles : 50,
	showThreshold : 50,
	validName : false,
	fileFilter : false,
	backupSuffix : '~',
	sync : 0,
	syncStart : true,
	loadTmbs : 5,
	cookie         : {
		expires  : 30,
		domain   : '',
		path     : '/',
		secure   : false,
		samesite : 'lax'
	},
	contextmenu : {
		navbar : ['open', 'opennew', 'download', '|', 'upload', 'mkdir', '|', 'copy', 'cut', 'paste', 'duplicate', '|', 'rm', 'empty', 'hide', '|', 'rename', '|', 'archive', '|', 'places', 'info', 'chmod', 'netunmount'],
		cwd    : ['undo', 'redo', '|', 'back', 'up', 'reload', '|', 'upload', 'mkdir', 'mkfile', 'paste', '|', 'empty', 'hide', '|', 'view', 'sort', 'selectall', 'colwidth', '|', 'places', 'info', 'chmod', 'netunmount', '|', 'fullscreen', '|', 'preference'],
		files  : ['seeComments' , '|' ,'sendToMondayChat', 'sendToThursdayChat',  'lockFile' ,'|' , 'getfile', '|' ,'open', 'opennew', 'download', 'opendir', 'quicklook', '|', 'upload', 'mkdir', '|', 'copy', 'cut', 'paste', '|' , 'mv' , 'duplicate', '|', 'rm', 'empty', 'hide', '|', 'rename', 'edit', 'resize', '|', 'archive', 'extract', '|', 'selectall', 'selectinvert', '|', 'places', 'info', 'chmod', 'netunmount']
	},
	enableAlways : false,
	enableByMouseOver : true,
	windowCloseConfirm : ['hasNotifyDialog', 'editingFile'],
	rawStringDecoder : typeof Encoding === 'object' && typeof Encoding.convert === 'function'? function(str) {
		return Encoding.convert(str, {
			to: 'UNICODE',
			type: 'string'
		});
	} : null,
	debug : ['error', 'warning', 'event-destroy'],
	toastBackendWarn : true,
	enableRootRename : true,
};
