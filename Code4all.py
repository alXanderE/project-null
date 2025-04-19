from flask import Flask, request, render_template, jsonify

app = Flask(__name__)

# Landing page
@app.route("/")
def landing():
    return render_template("index.html")

# Editor page
@app.route("/editor")
def editor():
    return render_template("editor.html")

# Echo endpoint (plain text)
@app.route("/echo", methods=["POST"])
def echo():
    text = request.get_data(as_text=True)
    return text, 200, { "Content-Type": "text/plain; charset=UTF-8" }

# JSON content endpoint
@app.route("/api/courses/<int:course_id>/content", methods=["GET", "POST"])
def course_content(course_id):
    if request.method == "GET":
        # stub: return empty or dummy content
        return jsonify(content=""), 200

    # POST: save incoming JSON
    data = request.get_json()
    content = data.get("content", "")
    # TODO: save to DB…
    return jsonify(message="Content saved"), 200

if __name__ == "__main__":
    app.run(debug=True, port=8080)