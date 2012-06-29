function AndNode(l, r) {
	this.left = l;	
	this.right = r;
	this.fulfill = function(taken) {
		return this.left.fulfill(taken) && this.right.fulfill(taken);
	}
	this.strform = function() {
		return "("+this.left.strform()+" && "+this.right.strform()+")";
	}
}

function OrNode(l, r) {
	this.left = l;
	this.right = r;
	this.fulfill = function(taken) {
		return this.left.fulfill(taken) || this.right.fulfill(taken);
	}
	this.strform = function() {
		return "("+this.left.strform()+" || "+this.right.strform()+")";
	}
}

function ClassNode(l, r) {
	this.left = l;
	this.right = r;
	this.fulfill = function(taken) {
		ourclass = this.left + " " + this.right;
		// document.write(ourclass + " in? "+taken+"<br />");
		return taken.search(ourclass) != -1;
	}
	this.strform = function() {
		return this.left+" "+this.right;
	}
}

function EmptyNode() {
	this.fulfill = function(taken) {
		return true;
	}
	this.strform = function() {
		return "None";
	}
}

/*
* Input: a string
* Output: a two element array of the form
* [token, remainder]
* where token is any of DEPT NUM, AND, OR, and remainder is the rest of the string
*/
function nextToken(string) {
	var ind = string.indexOf(' ');
	if (ind == -1) { // Assume last token, return it
		return [string, ""];
	}
	else {
		return [string.substring(0, ind), string.substring(ind+1)];
	}
}

/*
* Input: an array of class requirements in postfix notation
* Output: Recursively calculates the tree structure of this string
*/
function buildTree(reqarray) {
	var token;
	var left, right;
	var out;
	
	token = reqarray.pop();
	if (token == "AND") {
		left = buildTree(reqarray);
		right = buildTree(reqarray);
		out = new AndNode(left, right);
		//document.write(out.strform()+"<br />");
		return out;
	}
	else if (token == "OR") {
		left = buildTree(reqarray);
		right = buildTree(reqarray);
		out = new OrNode(left, right);
		//document.write(out.strform()+"<br />");
		return out;
	}
	else { // Token is a class number hopefully
		right = token;
		left = reqarray.pop();
		out = new ClassNode(left, right);
		//document.write(out.strform()+"<br />");
		return out;
	}
}

/*
* Input: a string taken from the prereqs section of a Course node
* Output: a tree representing that prereqs, suitable for calling fulfill on
*/
function getReqTree(courseReqs) {
	// Check for no prereqs
	if (courseReqs == "")
		return new EmptyNode();
	
	// First, normalize the string
	var modstr = courseReqs;
	modstr = modstr.replace(/AND/g, " AND");
	modstr = modstr.replace(/OR/g, " OR");
	modstr = modstr.replace(/\(/g, "( ");
	modstr = modstr.replace(/\)/g, " )");
	
	// Now, run the Shunting-yard algorithm on our string
	var out = [];
	var opstack = [];
	var retval;
	var token;
	
	while (modstr != "") {
		retval = nextToken(modstr);
		token = retval[0];
		modstr = retval[1];
		
		if (token == "AND" || token == "OR") {
			while (opstack.length > 0 && opstack[opstack.length-1] != "(")
				out.push(opstack.pop());
			opstack.push(token);
		}
		else if (token == "(") {
			opstack.push(token);
		}
		else if (token == ")") {
			while (opstack[opstack.length-1] != "(") {
				out.push(opstack.pop());
				if (opstack.length == 0) { // Mismatched parenthesis
					alert("Mismatch");
					return null;
				}
			}
			opstack.pop();
		}
		else {
			out.push(token);
		}
	}
	// No more tokens
	while (opstack.length > 0)
		out.push(opstack.pop());
	
	// Now we have a postfix notation string, begin building our tree recursively
	var tree = buildTree(out);
	return tree;
	
}

//document.write(getReqTree("(COMP 211OR COMP 215)AND (COMP 182OR COMP 280)").fulfill("COMP 211 COMP 280"));
