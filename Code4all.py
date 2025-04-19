from flask import Flask, request, render_template, jsonify, Response
from flask_sqlalchemy import SQLAlchemy


app = Flask(__name__)
app.config['SQLALCHEMY_DATABASE_URI'] = "mysql+pymysql://root:GavinLeeDlrkdgus%4012@localhost/mydb"
db = SQLAlchemy(app)

# Python SQLAlchemy example
class Page(db.Model):
    id = db.Column(db.Integer, primary_key=True)
    slug = db.Column(db.String(255), unique=True)
    html = db.Column(db.Text)

    # Add your landing page
with app.app_context():
    db.create_all()
    with open("templates/index.html", encoding="utf-8") as f:
            landing_html = f.read()
    # Check if "landing" page already exists
    existing = Page.query.filter_by(slug="landing").first()
    if not existing:
        page = Page(slug="landing", html=landing_html)
        db.session.add(page)    
        db.session.commit()
    elif existing.html != landing_html:
        # Update the existing page with new content
        existing.html = landing_html
        print("Updating existing page")
    db.session.commit()
    
@app.route('/')
def serve_landing_from_db():
    page = Page.query.filter_by(slug="landing").first()
    if page:
        return Response(page.html, mimetype='text/html')
    return "Page not found", 404

# # Editor page
@app.route("/editor")
def editor():
    return render_template("content-maker.html")


if __name__ == "__main__":
    app.run(host='0.0.0.0', port=8080)