import{r,j as e,R as k,a as j}from"./client.js";function w(){const[s,o]=r.useState([]),[n,d]=r.useState(""),[c,l]=r.useState(""),[m,u]=r.useState(!1),[f,i]=r.useState(""),[p,g]=r.useState("");r.useEffect(()=>{b()},[]);async function b(){try{const t=await fetch("/api/list_tickets.php",{credentials:"same-origin"});if(t.status===401){o([]),i("");return}if(!t.ok)throw new Error;const a=await t.json();o(a),i("")}catch{i("Could not load tickets.")}}async function h(t){t.preventDefault(),u(!0),g("");try{const a=await fetch("/api/create_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},credentials:"same-origin",body:JSON.stringify({title:n,message:c})}),x=await a.json();if(!a.ok)throw new Error(x.error||"Submission failed");d(""),l(""),await b()}catch(a){g(a.message)}finally{u(!1)}}return e.jsxs(e.Fragment,{children:[e.jsx("style",{children:`
        * {
          box-sizing: border-box;
          margin: 0;
          padding: 0;
          font-family: 'Kumbh Sans', sans-serif;
        }

        body {
          background: #141414;
          color: #fff;
        }

        .page {
          max-width: 1200px;
          margin: 0 auto;
          padding: 4rem 1rem;
        }

        h1 {
          text-align: center;
          font-size: 2.5rem;
          margin-bottom: 3rem;
          background: linear-gradient(to top, #ff0844, #ffb199);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }

        h2 {
          margin-bottom: 1.2rem;
          font-size: 1.4rem;
        }

        .card {
          background: #1f1f1f;
          border-radius: 8px;
          padding: 2rem;
          margin-bottom: 2.5rem;
        }

        input, textarea {
          width: 100%;
          background: #141414;
          border: 1px solid #2a2a2a;
          border-radius: 6px;
          padding: 14px;
          color: #fff;
          margin-bottom: 1rem;
          font-size: 1rem;
        }

        textarea {
          min-height: 120px;
        }

        button {
          width: 100%;
          background: linear-gradient(to right, #f77062, #fe5196);
          border: none;
          padding: 14px;
          border-radius: 6px;
          color: #fff;
          cursor: pointer;
        }

        button:disabled {
          opacity: 0.6;
        }

        .error {
          color: #ff4d4d;
          margin-bottom: 1rem;
        }

        .ticket {
          background: #141414;
          border-radius: 6px;
          padding: 1.2rem;
          margin-bottom: 1rem;
        }

        .ticket-header {
          display: flex;
          justify-content: space-between;
          margin-bottom: 0.4rem;
        }

        .ticket-title {
          font-weight: 600;
          background: linear-gradient(to top, #f77062, #fe5196);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }

        .status {
          font-size: 0.75rem;
          padding: 4px 10px;
          border-radius: 999px;
          background: #1f1f1f;
          color: #f77062;
        }

        .empty {
          color: #aaa;
        }
      `}),e.jsxs("div",{className:"page",children:[e.jsx("h1",{children:"Support Dashboard"}),e.jsxs("div",{className:"card",children:[e.jsx("h2",{children:"Create Ticket"}),p&&e.jsx("div",{className:"error",children:p}),e.jsxs("form",{onSubmit:h,children:[e.jsx("input",{value:n,onChange:t=>d(t.target.value),placeholder:"Title",required:!0}),e.jsx("textarea",{value:c,onChange:t=>l(t.target.value),placeholder:"Message",required:!0}),e.jsx("button",{disabled:m,children:m?"Submitting…":"Submit"})]})]}),e.jsxs("div",{className:"card",children:[e.jsx("h2",{children:"Your Tickets"}),f&&e.jsx("div",{className:"error",children:f}),s.length===0?e.jsx("div",{className:"empty",children:"No tickets submitted yet."}):s.map(t=>e.jsxs("div",{className:"ticket",children:[e.jsxs("div",{className:"ticket-header",children:[e.jsx("div",{className:"ticket-title",children:t.title}),e.jsx("div",{className:"status",children:t.status})]}),e.jsx("div",{children:t.message})]},t.id))]})]})]})}k.createRoot(document.getElementById("root")).render(e.jsx(j.StrictMode,{children:e.jsx(w,{})}));
