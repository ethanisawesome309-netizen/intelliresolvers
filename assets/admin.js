import{r as a,l as $,j as t,c as P}from"./index2.js";function R(){const[c,u]=a.useState([]),[p,T]=a.useState([]),[k,x]=a.useState(""),[o,S]=a.useState(""),[g,I]=a.useState(null),[v,C]=a.useState(""),[E,_]=a.useState(null),[b,z]=a.useState([]),[H,y]=a.useState(!1),[O,U]=a.useState(""),w=(e,r)=>{if(r===null||r===""||r==="0")return"None";const s={status_id:{1:"Open",2:"In Progress",3:"Closed"},priority_id:{1:"Low",2:"Medium",3:"High",4:"Urgent"}};if(s[e]&&s[e][r])return s[e][r];if(e==="assigned_to"||e==="claimed_by"){const l=(p||[]).find(i=>i&&parseInt(i.id)===parseInt(r));return l?l.name:`User ${r}`}return r};a.useEffect(()=>{const e=$({path:"/socket.io/",transports:["websocket","polling"],reconnectionAttempts:5});return j(),D(),e.on("connect",()=>{console.log("✅ Connected to WebSocket Server (ID:",e.id,")")}),e.on("refresh_tickets",r=>{console.log("📩 Real-time update received:",r),r.ticket_id&&(_(r.ticket_id),setTimeout(()=>_(null),3e3)),j()}),()=>{e.disconnect(),e.off("connect"),e.off("refresh_tickets")}},[]);async function m(e,r={}){const s=await fetch(e,{credentials:"include",...r}),l=await s.text();try{return{res:s,data:JSON.parse(l)}}catch{throw new Error("Invalid server response.")}}async function A(e){U(e.title);try{const{res:r,data:s}=await m(`/api/get_ticket_history.php?ticket_id=${e.id}`);if(r.status===404){alert("Error: /api/get_ticket_history.php was not found. Please check file placement.");return}s.success&&(z(s.data),y(!0))}catch(r){alert("Fetch failed: "+r.message)}}async function j(){try{const{res:e,data:r}=await m("/api/admin_list_tickets.php");if(!e.ok||r.success===!1)throw new Error(r.error||`HTTP ${e.status}`);u(r.tickets||[]),r.tier&&S(r.tier),r.current_user_id&&I(parseInt(r.current_user_id)),r.user_email&&C(r.user_email)}catch(e){x(`Load Error: ${e.message}`)}}async function D(){try{const{data:e}=await m("/api/list_developers.php");e.success&&T(e.developers||[])}catch{console.error("Could not load developers")}}async function h(e,r,s){const l=[...c],i=s===""?null:parseInt(s);u(d=>d.map(n=>n.id===e?{...n,[r]:i}:n));try{const{res:d,data:n}=await m("/api/patch_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:e,[r]:i})});if(!d.ok||n.success===!1)throw new Error(n.error||"Update failed")}catch(d){u(l),x(`Update failed: ${d.message}`)}}async function J(e){if(confirm("Delete this ticket permanently?"))try{const{data:r}=await m("/api/delete_ticket.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify({id:e})});if(r.success)j();else throw new Error(r.error||"Delete failed")}catch(r){x(`Delete failed: ${r.message}`)}}const f={total:c.length,open:c.filter(e=>parseInt(e.status_id)===1).length,urgent:c.filter(e=>parseInt(e.priority_id)===4).length,unassigned:c.filter(e=>!e.assigned_to&&!e.claimed_by).length},L={Senior:p.filter(e=>e.role==="Senior"),Intermediate:p.filter(e=>e.role==="Intermediate"),Junior:p.filter(e=>e.role==="Junior"),Other:p.filter(e=>!["Senior","Intermediate","Junior"].includes(e.role))};return t.jsxs(t.Fragment,{children:[t.jsx("style",{children:`
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

        .header-flex {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 3rem;
        }

        .title-group {
          display: flex;
          flex-direction: column;
          gap: 0.5rem;
        }

        h1 {
          font-size: 2.5rem;
          background: linear-gradient(to top, #ff0844, #ffb199);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }

        .tier-badge {
          display: inline-block;
          align-self: flex-start;
          padding: 4px 12px;
          border-radius: 20px;
          font-size: 0.75rem;
          font-weight: bold;
          text-transform: uppercase;
          background: rgba(255, 255, 255, 0.05);
          color: #f77062;
          border: 1px solid #f77062;
          letter-spacing: 1px;
        }

        .logout-btn {
          background: #ff4d4d;
          color: white;
          padding: 10px 20px;
          text-decoration: none;
          border-radius: 4px;
          font-weight: bold;
        }

        .summary-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 1.5rem;
          margin-bottom: 3rem;
        }

        .stat-card {
          background: #1f1f1f;
          padding: 1.5rem;
          border-radius: 8px;
          text-align: center;
        }

        .stat-card h3 {
          font-size: 0.8rem;
          color: #aaa;
          margin-bottom: 0.5rem;
          text-transform: uppercase;
        }

        .stat-number {
          font-size: 2rem;
          font-weight: bold;
          color: #fe5196;
        }

        .card {
          background: #1f1f1f;
          border-radius: 8px;
          padding: 2rem;
          margin-bottom: 2.5rem;
        }

        .ticket {
          background: #141414;
          border-radius: 6px;
          padding: 1.2rem;
          margin-bottom: 1rem;
          border-left: 4px solid #333;
          transition: all 0.5s ease;
        }

        .ticket.highlight {
          border-left-width: 10px;
          box-shadow: 0 0 20px rgba(254, 81, 150, 0.4);
          transform: scale(1.02);
          background: #1a1a1a;
        }

        .ticket-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 0.4rem;
        }

        .ticket-title {
          font-weight: 600;
          background: linear-gradient(to top, #f77062, #fe5196);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
        }

        .attachment-box {
          margin-top: 10px;
          padding: 8px;
          background: rgba(255, 255, 255, 0.03);
          border-radius: 4px;
          border: 1px dashed #444;
          font-size: 0.85rem;
        }

        .file-link {
          color: #3498db;
          text-decoration: none;
          display: flex;
          align-items: center;
          gap: 5px;
        }

        .file-link:hover {
          text-decoration: underline;
        }

        .assignment-indicator {
          font-size: 0.8rem;
          color: #2ecc71;
          margin-top: 5px;
          display: block;
          font-style: italic;
        }

        .controls-row { 
          display: flex; 
          gap: 10px; 
          margin-top: 1rem; 
          flex-wrap: wrap; 
          align-items: center;
        }

        select, button { 
          background: #1f1f1f; 
          border: 1px solid #333; 
          color: #fff; 
          padding: 8px; 
          border-radius: 4px; 
          cursor: pointer; 
        }

        select:disabled, button:disabled {
          opacity: 0.3;
          cursor: not-allowed;
          filter: grayscale(1);
        }

        .claim-btn {
          background: #2ecc71;
          color: #fff;
          font-weight: bold;
          border: none;
        }

        .release-btn {
          background: #e67e22;
          color: #fff;
          font-weight: bold;
          border: none;
        }

        .priority-tag { 
          font-size: 0.7rem; 
          padding: 2px 8px; 
          border-radius: 10px; 
          text-transform: uppercase; 
          margin-right: 10px; 
          vertical-align: middle; 
        }

        .danger { 
          color: #ff4d4d; 
          border-color: #442222; 
        }

        optgroup {
          background: #1f1f1f;
          color: #f77062;
          font-style: normal;
          font-weight: bold;
        }

        /* ADDED MODAL STYLES */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); display: flex; justify-content: center; align-items: center; z-index: 1000; }
        .modal-content { background: #1f1f1f; padding: 2rem; border-radius: 12px; width: 500px; max-height: 80vh; overflow-y: auto; border: 1px solid #333; }
        .history-item { border-left: 2px solid #fe5196; padding-left: 10px; margin-bottom: 15px; }
        .val-tag { background: #000; padding: 2px 4px; color: #fe5196; font-family: monospace; }
      `}),t.jsxs("div",{className:"page",children:[t.jsxs("div",{className:"header-flex",children:[t.jsxs("div",{className:"title-group",children:[t.jsx("h1",{children:"IntelliResolver Ops"}),o&&t.jsxs("div",{className:"tier-badge",children:[o," Access Level"]})]}),t.jsx("a",{href:"/logout.php",className:"logout-btn",children:"Logout"})]}),t.jsxs("div",{className:"summary-grid",children:[t.jsxs("div",{className:"stat-card",children:[t.jsx("h3",{children:"Active"}),t.jsx("div",{className:"stat-number",children:f.total})]}),t.jsxs("div",{className:"stat-card",style:{borderTop:"3px solid #e74c3c"},children:[t.jsx("h3",{children:"Urgent"}),t.jsx("div",{className:"stat-number",children:f.urgent})]}),t.jsxs("div",{className:"stat-card",style:{borderTop:"3px solid #f1c40f"},children:[t.jsx("h3",{children:"Unassigned"}),t.jsx("div",{className:"stat-number",children:f.unassigned})]}),t.jsxs("div",{className:"stat-card",style:{borderTop:"3px solid #2ecc71"},children:[t.jsx("h3",{children:"Open"}),t.jsx("div",{className:"stat-number",children:f.open})]})]}),k&&t.jsx("div",{style:{color:"#ff4d4d",marginBottom:"1rem"},children:k}),t.jsx("div",{className:"card",children:c.map(e=>{const r=E===parseInt(e.id),s=!e.claimed_by&&(e.assigned_to===null||parseInt(e.assigned_to)===g)&&v!=="admin@intelliresolvers.com",l=parseInt(e.claimed_by)===g;return t.jsxs("div",{className:`ticket ${r?"highlight":""}`,style:{borderLeftColor:e.priority_color},children:[t.jsxs("div",{className:"ticket-header",children:[t.jsxs("div",{children:[t.jsx("span",{className:"priority-tag",style:{background:e.priority_color},children:e.priority_label}),t.jsxs("span",{className:"ticket-title",children:["#",e.id," — ",e.title]}),e.claimed_by_name?t.jsxs("span",{className:"assignment-indicator",children:["✓ Claimed by: ",e.claimed_by_name," ",parseInt(e.claimed_by)===g?"(You)":""]}):e.assigned_to_name?t.jsxs("span",{className:"assignment-indicator",style:{color:"#3498db"},children:["ℹ Assigned to: ",e.assigned_to_name]}):null]}),t.jsx("div",{style:{color:"#888",fontSize:"0.8rem"},children:e.email})]}),t.jsx("div",{style:{margin:"1rem 0",color:"#ccc",lineHeight:"1.6"},children:e.message}),e.file_path&&t.jsxs("div",{className:"attachment-box",children:[t.jsx("strong",{children:"📎 Attachment:"}),t.jsx("a",{href:`/${e.file_path}`,target:"_blank",rel:"noopener noreferrer",className:"file-link",children:"View uploaded file"})]}),t.jsxs("div",{className:"controls-row",children:[s&&t.jsx("button",{className:"claim-btn",onClick:()=>h(e.id,"claimed_by",g),children:"Claim Ticket"}),l&&t.jsx("button",{className:"release-btn",onClick:()=>h(e.id,"claimed_by",""),children:"Release Ticket"}),t.jsxs("select",{value:e.assigned_to||"",onChange:i=>h(e.id,"assigned_to",i.target.value),disabled:o==="Junior"||o==="Intermediate",children:[t.jsx("option",{value:"",children:"Unassigned"}),Object.entries(L).map(([i,d])=>d.length>0&&t.jsx("optgroup",{label:`${i} Tier`,children:d.map(n=>t.jsx("option",{value:n.id,children:n.name},n.id))},i))]}),t.jsxs("select",{value:e.status_id,onChange:i=>h(e.id,"status_id",i.target.value),disabled:v==="admin@intelliresolvers.com",children:[t.jsx("option",{value:"1",children:"Open"}),t.jsx("option",{value:"2",children:"In Progress"}),t.jsx("option",{value:"3",children:"Closed"})]}),t.jsxs("select",{value:e.priority_id,onChange:i=>h(e.id,"priority_id",i.target.value),disabled:o==="Junior"||o==="Intermediate",children:[t.jsx("option",{value:"1",children:"Low"}),t.jsx("option",{value:"2",children:"Medium"}),t.jsx("option",{value:"3",children:"High"}),t.jsx("option",{value:"4",children:"Urgent"})]}),t.jsx("button",{onClick:()=>A(e),children:"📜 History"}),t.jsx("button",{className:"danger",onClick:()=>J(e.id),disabled:o==="Junior"||o==="Intermediate",children:"Archive"})]})]},e.id)})})]}),H&&t.jsx("div",{className:"modal-overlay",onClick:()=>y(!1),children:t.jsxs("div",{className:"modal-content",onClick:e=>e.stopPropagation(),children:[t.jsxs("h3",{children:["History: ",O]}),t.jsx("div",{style:{marginTop:"20px"},children:!b||b.length===0?t.jsx("p",{children:"No changes recorded yet."}):b.map(e=>t.jsxs("div",{className:"history-item",children:[t.jsx("div",{style:{fontSize:"0.7rem",color:"#777"},children:e.changed_at}),t.jsxs("div",{children:[t.jsx("strong",{children:e.changed_by})," changed ",e.field_changed.replace("_id","")]}),t.jsxs("div",{style:{fontSize:"0.8rem"},children:[t.jsx("span",{className:"val-tag",children:w(e.field_changed,e.old_value)})," → ",t.jsx("span",{className:"val-tag",children:w(e.field_changed,e.new_value)})]})]},e.id))}),t.jsx("button",{style:{marginTop:"20px",width:"100%",padding:"10px"},onClick:()=>y(!1),children:"Close"})]})})]})}const N=document.getElementById("root");N&&P.createRoot(N).render(t.jsx(R,{}));
