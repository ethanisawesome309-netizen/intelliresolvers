import{r as a,l as y,j as e,R as v,a as S}from"./index2.js";function N(){const[d,o]=a.useState([]),[m,p]=a.useState(""),[f,u]=a.useState(""),[i,h]=a.useState(null),[b,g]=a.useState(!1),[x,n]=a.useState(""),[k,j]=a.useState("");a.useEffect(()=>{l();const t=y({path:"/socket.io/",transports:["websocket","polling"]});return t.on("refresh_tickets",r=>{r.field==="delete_ticket"?o(s=>s.filter(c=>parseInt(c.id)!==parseInt(r.ticket_id))):l()}),()=>t.disconnect()},[]);async function l(){try{const t=await fetch("/api/list_tickets.php",{credentials:"same-origin"});if(t.status===401){o([]),n("");return}if(!t.ok)throw new Error;const r=await t.json();o(r),n("")}catch{n("Could not load tickets.")}}async function w(t){t.preventDefault(),g(!0),j("");try{const r=new FormData;r.append("title",m),r.append("message",f),i&&r.append("attachment",i);const s=await fetch("/api/create_ticket.php",{method:"POST",credentials:"same-origin",body:r}),c=await s.json();if(!s.ok)throw new Error(c.error||"Submission failed");p(""),u(""),h(null),await l()}catch(r){j(r.message)}finally{g(!1)}}return e.jsxs(e.Fragment,{children:[e.jsx("style",{children:`
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

        /* ✅ STYLED UPLOAD BUTTON */
        .file-upload-wrapper {
          margin-bottom: 1.5rem;
        }

        .custom-file-upload {
          display: inline-block;
          padding: 10px 20px;
          cursor: pointer;
          background: #2a2a2a;
          border: 1px dashed #f77062;
          border-radius: 6px;
          color: #f77062;
          font-size: 0.9rem;
          transition: all 0.3s ease;
        }

        .custom-file-upload:hover {
          background: #333;
          border-style: solid;
        }

        .file-name {
          margin-left: 10px;
          font-size: 0.8rem;
          color: #aaa;
        }

        button.submit-btn {
          width: 100%;
          background: linear-gradient(to right, #f77062, #fe5196);
          border: none;
          padding: 14px;
          border-radius: 6px;
          color: #fff;
          cursor: pointer;
          font-weight: bold;
          font-size: 1rem;
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
          border-left: 3px solid #f77062;
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

        .attachment-link {
          display: inline-block;
          margin-top: 10px;
          color: #3498db;
          text-decoration: none;
          font-size: 0.85rem;
        }

        .attachment-link:hover {
          text-decoration: underline;
        }

        .empty {
          color: #aaa;
        }
      `}),e.jsxs("div",{className:"page",children:[e.jsx("h1",{children:"Support Dashboard"}),e.jsxs("div",{className:"card",children:[e.jsx("h2",{children:"Create Ticket"}),k&&e.jsx("div",{className:"error",children:k}),e.jsxs("form",{onSubmit:w,children:[e.jsx("input",{value:m,onChange:t=>p(t.target.value),placeholder:"Title",required:!0}),e.jsx("textarea",{value:f,onChange:t=>u(t.target.value),placeholder:"Message",required:!0}),e.jsxs("div",{className:"file-upload-wrapper",children:[e.jsxs("label",{className:"custom-file-upload",children:[e.jsx("input",{type:"file",style:{display:"none"},onChange:t=>h(t.target.files[0])}),i?"Change File":"📎 Attach Log or Screenshot"]}),i&&e.jsx("span",{className:"file-name",children:i.name})]}),e.jsx("button",{type:"submit",className:"submit-btn",disabled:b,children:b?"Submitting…":"Submit Ticket"})]})]}),e.jsxs("div",{className:"card",children:[e.jsx("h2",{children:"Your Tickets"}),x&&e.jsx("div",{className:"error",children:x}),d.length===0?e.jsx("div",{className:"empty",children:"No tickets submitted yet."}):d.map(t=>e.jsxs("div",{className:"ticket",children:[e.jsxs("div",{className:"ticket-header",children:[e.jsx("div",{className:"ticket-title",children:t.title}),e.jsx("div",{className:"status",children:t.status})]}),e.jsx("div",{style:{color:"#ccc",fontSize:"0.95rem"},children:t.message}),t.file_path&&e.jsx("a",{href:`/${t.file_path}`,target:"_blank",rel:"noopener noreferrer",className:"attachment-link",children:"View Attachment ↗"})]},t.id))]})]})]})}v.createRoot(document.getElementById("root")).render(e.jsx(S.StrictMode,{children:e.jsx(N,{})}));
