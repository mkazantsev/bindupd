links = ['view', 'edit', 'add'];
views = ['viewContent', 'editContent', 'addContent'];

function set(pressedVal) {
	var index = 0;
	for (var i = 0; i < links.length; i++)
		if (pressedVal == links[i])
			index = i;

	if (document.getElementById(links[index]).className.indexOf('active') != -1)
		return;

	for (var i = 0; i < links.length; i++) {
		document.getElementById(links[i]).className = (i == index ? "active" : "");
		document.getElementById(views[i]).style.display = (i == index ? "block" : "none");
	}
}
